/// <reference path="./includes/media-html.ts" />

namespace WpMarkdownExtraEditor {
  const $: JQueryStatic = jQuery;

  export class Editor {
    /*
     * Properties.
     */
    public data: Data;

    public cns: string;
    public ens: string;

    protected mediaHtml: MediaHtml;
    protected initChain: JQueryPromise<any>;
    protected setupChain: JQueryPromise<any>;

    public $win: JQuery;
    public $doc: JQuery;

    public $html: JQuery;
    public $body: JQuery;

    public $form: JQuery;
    public $area: JQuery;
    public $wrapper: JQuery;

    public $toolbar: JQuery;
    public $toolbarBtns: JQuery;

    public $container: JQuery;
    public $textarea: JQuery;
    public $ide: JQuery;
    public ide: any;

    public $preview: JQuery;
    public $loadingStyles: JQuery;
    public $loading: JQuery;

    public $statusInfo: JQuery;
    public $textareaResizeHandle: JQuery;

    public $previewBtn: JQuery;
    public $fullscreenSplitPreviewBtn: JQuery;
    public $sitePreviewBtn: JQuery;
    public $fullscreenBtn: JQuery;

    public $saveDraftBtn: JQuery;
    public $publishBtn: JQuery;
    public $updateBtn: JQuery;
    public $trashBtn: JQuery;

    public themeClass: string;
    public previewClass: string;

    public fullscreenClass: string;
    public fullscreenPreviewClass: string;
    public fullscreenSplitPreviewClass: string;

    public $previewWindow: JQuery;
    public $previewDocument: JQuery;
    public $previewBody: JQuery;
    public $previewDiv: JQuery;

    protected _scrollbarWidth: number;
    protected scrollLockHandler: (_: any) => any;
    protected previewScrollSyncHandler: (_: any) => any;
    protected previewXhr: XMLHttpRequest;

    public currentMode: string;

    /*
     * Constructor.
     */

    constructor() {
      // This long global comes from PHP.
      // It contains instance-specific config data.
      this.data = sxz4aq7w68twt86g8ye5m3np7nrtguw8EditorData;

      this.cns = this.data.brand.slug + '-editor',
        this.ens = '.' + this.data.brand.slug + '-editor';

      this.mediaHtml = new MediaHtml(this);

      this.initChain = $.Deferred().resolve().promise(),
        this.setupChain = $.Deferred().resolve().promise();

      $(document).ready(() => this.onDomReady());
    }

    /*
     * On DOM ready.
     */

    protected onDomReady() {
      this.$textarea = $('.' + this.cns + '-textarea');
      if (this.$textarea.length) this.init();
    }

    /*
     * Init routines.
     */

    protected init() {
      this.initChain
        .then(() => this.initClasses())
        .then(() => this.initElements())
        .then(() => this.initIde())
        .then(() => this.initPreview())
        .then(() => this.initComplete())
        .then(() => this.setup());
    }

    protected initClasses() {
      this.themeClass = this.cns + '-' + this.data.settings.theme + '-theme';
      this.previewClass = this.cns + '-preview-mode';

      this.fullscreenClass = this.cns + '-fullscreen-mode';
      this.fullscreenPreviewClass = this.cns + '-fullscreen-preview-mode';
      this.fullscreenSplitPreviewClass = this.cns + '-fullscreen-split-preview-mode';
    }

    protected initElements() {
      this.$win = $(window), this.$doc = $(document);

      this.$html = $('html'), this.$body = $('body'),
        this.$html.addClass(this.themeClass);

      if (this.data.settings.ideEnable) {
        this.$html.addClass(this.cns + '-has-ide');
      }
      this.$form = this.$textarea.closest('form#post'),
        this.$form.addClass(this.cns + '-form');

      this.$area = this.$form.find('#postdivrich'),
        this.$area.addClass(this.cns + '-area');

      this.$wrapper = this.$area.find('#wp-content-wrap'),
        this.$wrapper.addClass(this.cns + '-wrapper');

      this.$toolbar = this.$area.find('#wp-content-editor-tools'),
        this.$toolbarBtns = $('<div class="' + _.escape(this.cns + '-toolbar-btns') + '"></div>');

      this.$toolbar.css({ visibility: 'hidden' }),
        this.$toolbar.find('.wp-editor-tabs').hide(),
        this.$toolbar.addClass(this.cns + '-toolbar')
          .append(this.$toolbarBtns);

      this.$container = this.$area.find('#wp-content-editor-container'),
        this.$container.css({ visibility: 'hidden' }),
        this.$container.addClass(this.cns + '-container');

      if (this.data.settings.ideEnable) { // Enable IDE for enhanced editing?
        this.$ide = $('<textarea class="' + _.escape(this.cns + '-ide') + '"></textarea>'),
          this.$textarea.before(this.$ide); // Insert before editor.
      }
      this.$statusInfo = this.$area.find('#post-status-info'),
        this.$statusInfo.css({ visibility: 'hidden' }),
        this.$statusInfo.addClass(this.cns + '-status-info');

      this.$textareaResizeHandle = this.$area.find('#content-resize-handle'),
        this.$textareaResizeHandle.hide(); // Disallow use of this core resizer.

      this.$preview = $('<iframe class="' + _.escape(this.cns + '-preview') + '" src="' + _.escape(this.data.settings.previewUrl) + '"></iframe>');

      this.$loadingStyles = this.$area.find('.' + this.cns + '-loading-styles'),
        this.$loading = this.$area.find('.' + this.cns + '-loading');
    }

    protected initIde() {
      if (!this.data.settings.ideEnable) {
        return; // IDE not enabled.
      } // i.e., Disabled via options.

      this.ide = ace.edit(this.$ide[ 0 ]);

      if (this.data.settings.ide.maxLines === 'Infinity') {
        this.data.settings.ide.maxLines = Infinity;
      } // Set JavaScript `Infinity` value.

      this.ide.setOptions(this.data.settings.ide);
      this.ide.setStyle(this.cns + '-ide');
      this.ide.$blockScrolling = Infinity;

      this.docValue(this.$textarea.val());
      this.$textarea.addClass('-position-offscreen');
      this.$ide = this.$container.find('.' + this.cns + '-ide');
    }

    protected initPreview() {
      let deferred = $.Deferred((deferred) => {
        this.$preview.on('load' + this.ens, () => {
          let iframe = <HTMLIFrameElement>this.$preview[ 0 ];

          this.$previewWindow = $(iframe.contentWindow || document.defaultView);
          this.$previewDocument = $(iframe.contentDocument || iframe.contentWindow.document);
          this.$previewBody = this.$previewDocument.find('body');
          this.$previewDiv = this.$previewBody.find('#___div');

          let $body = this.$previewBody; // Shorter reference.

          if (this.data.settings.hljsStyleUrl) {
            let href = this.data.settings.hljsStyleUrl,
              integrity = ' integrity="' + _.escape(this.data.settings.hljsStyleSri) + '" crossorigin="anonymous"';
            $body.append('<link type="text/css" rel="stylesheet" href="' + _.escape(href) + '"' + integrity + ' />');
          }
          if (this.data.settings.previewStylesUrl) {
            let href = this.data.settings.previewStylesUrl,
              integrity = ' integrity="' + _.escape(this.data.settings.previewStylesSri) + '" crossorigin="anonymous"';
            $body.append('<link type="text/css" rel="stylesheet" href="' + _.escape(href) + '"' + integrity + ' />');
          }
          if (this.data.settings.hljsBgColor) {
            let background = this.data.settings.hljsBgColor;
            $body.append('<style>.hljs-pre > .hljs { background: ' + background.replace(/[<&>]/g, '') + ' !important; }</style>');
          }
          if (this.data.settings.hljsFontFamily) {
            let fontFamily = this.data.settings.hljsFontFamily;
            $body.append('<style>.hljs-pre > .hljs { font-family: ' + fontFamily.replace(/[<&>]/g, '') + ' !important; }</style>');
          }
          if (this.data.settings.customPreviewStyles) {
            let customPreviewStyles = this.data.settings.customPreviewStyles;
            $body.append('<style>' + customPreviewStyles + '</style>');
          }
          deferred.resolve(); // Done here.
        });
        this.$container.append(this.$preview);
      });
      return deferred.promise();
    }

    protected initComplete() {
      this.$area.trigger('initComplete' + this.ens);
    }

    /*
     * Setup routines.
     */

    protected setup() {
      this.setupChain
        .then(() => this.setupTextarea())
        .then(() => this.setupIde())
        .then(() => this.setupContainer())
        .then(() => this.setupMediaBtn())
        .then(() => this.setupToolbar())
        .then(() => this.setupComplete());
    }

    protected setupTextarea() {
      if (this.data.settings.ideEnable) {
        return; // Using the IDE.
      } // i.e., Enabled via options.

      this.scrollLockingEnable();

      this.$textarea.css({
        'font-size': this.data.settings.fontSize,
        'font-family': this.data.settings.fontFamily,
      });
      let previewDelay = this.data.settings.previewMethod === 'php' ? 1000 : 250,
        previewDebounce = _.debounce(() => this.previewRender(), previewDelay);

      this.$textarea.on('keyup' + this.ens, (e) => { this.automaticScrollExpand(e); });
      this.$textarea.on('cut' + this.ens + ' paste' + this.ens + ' keyup' + this.ens + ' change' + this.ens, previewDebounce);
    }

    protected setupIde() {
      if (!this.data.settings.ideEnable) {
        return; // IDE not enabled.
      } // i.e., Disabled via options.

      this.scrollLockingEnable();

      let updateDebounce = _.debounce(() => {
        this.$textarea.val(this.docValue());
        this.$textarea.trigger('input', 'ide-update' + this.ens);
      }, 1000); // `.trigger('input')` = compatibility w/ WP word-counter.

      let previewDelay = this.data.settings.previewMethod === 'php' ? 1000 : 250,
        previewDebounce = _.debounce(() => this.previewRender(), previewDelay);

      this.ide.session.on('change', updateDebounce),
        this.ide.session.on('change', previewDebounce);

      this.$textarea.on('input' + this.ens, (e: JQueryEventObject, via: string) => {
        if (via === 'ide-update' + this.ens) return;
        this.ide.focus(), this.docValue(this.$textarea.val());
      }); // e.g., Restoration via `wp.autosave` `execCommand()`.

      $('#post-preview').on('click' + this.ens, () => this.$textarea.val(this.docValue())),
        this.$form.on('submit' + this.ens, () => this.$textarea.val(this.docValue()));
    }

    protected setupContainer() {
      let delay = 250, // Always the same for container resizes.
        debounce = _.debounce((e: JQueryEventObject) => this.adjustContainer(e), delay);
      $(window).on('resize' + this.ens, debounce);
    }

    protected setupMediaBtn() {
      let sendToEditor = window.send_to_editor;

      window.send_to_editor = (html: string) => {
        html = this.mediaHtml.format(html);

        if (this.ide) { // IDE enabled?
          this.ide.insert(html); // Insert IDE markup.
        } else sendToEditor(html); // Core handler.
      };
    }

    protected setupToolbar() {
      // Generale all of the toolbar buttons.

      this.$previewBtn = $('<button type="button" class="-preview button" title="' + _.escape(this.data.i18n.preview + ' ' + this.data.i18n.toggle) + '"><span class="-icon sharkicon sharkicon-eye"></span></button>');
      this.$fullscreenSplitPreviewBtn = $('<button type="button" class="-fullscreen-split button" title="' + _.escape(this.data.i18n.fullscreenSplitPreview + ' ' + this.data.i18n.toggle) + '"><span class="-icon sharkicon sharkicon-columns"></span></button>');
      this.$sitePreviewBtn = $('<button type="button" class="-post-preview button" title="' + _.escape(this.data.i18n.sitePreview) + '"><span class="-icon sharkicon sharkicon-octi-link-external"></span></button>');
      this.$fullscreenBtn = $('<button type="button" class="-fullscreen button" title="' + _.escape(this.data.i18n.fullscreen + ' ' + this.data.i18n.toggle) + '"><span class="-icon sharkicon sharkicon-octi-screen-full"></span></button>');

      this.$saveDraftBtn = $('<button type="button" class="-save-draft button" title="' + _.escape(this.data.i18n.saveDraft) + '"><span class="-icon sharkicon sharkicon-floppy-o"></span></button>');
      this.$publishBtn = $('<button type="button" class="-publish button" title="' + _.escape(this.data.i18n.publish) + '"><span class="-icon sharkicon sharkicon-thumb-tack"></span></button>');
      this.$updateBtn = $('<button type="button" class="-update button" title="' + _.escape(this.data.i18n.update) + '"><span class="-icon sharkicon sharkicon-thumb-tack"></span></button>');
      this.$trashBtn = $('<button type="button" class="-trash button" title="' + _.escape(this.data.i18n.trash) + '"><span class="-icon sharkicon sharkicon-trash"></span></button>');

      // Build a list of visible targets.

      let $sitePreviewTarget = $('#preview-action:visible > #post-preview:visible'),
        $saveDraftTarget = $('#save-action:visible > #save-post:visible'),
        $publishTarget = $('#publishing-action:visible > #publish:visible'),
        $trashTarget = $('#delete-action:visible > a:visible');

      // Append buttons conditionally.

      this.$toolbarBtns.append(this.$previewBtn);
      this.$toolbarBtns.append(this.$fullscreenSplitPreviewBtn);

      if ($sitePreviewTarget.length) {
        this.$toolbarBtns.append(this.$sitePreviewBtn);
      }
      if ($saveDraftTarget.length) {
        this.$toolbarBtns.append(this.$saveDraftBtn);
      }
      if ($publishTarget.length) {
        if (this.data.settings.postId) {
          this.$toolbarBtns.append(this.$updateBtn);
        } else { // It's a new post.
          this.$toolbarBtns.append(this.$publishBtn);
        } // Based on there being a post ID.
      }
      if ($trashTarget.length) {
        this.$toolbarBtns.append(this.$trashBtn);
      }
      this.$toolbarBtns.append(this.$fullscreenBtn);

      // Setup button click handlers.

      this.$previewBtn.on('click' + this.ens, (e) => {
        e.stopImmediatePropagation(), e.preventDefault();

        switch (this.currentMode) {
          case 'default':
            this.preparePreviewMode();
            break;

          case 'preview':
            this.prepareDefaultMode();
            break;

          case 'fullscreen':
            this.prepareFullscreenPreviewMode();
            break;

          case 'fullscreen-preview':
            this.prepareFullscreenMode();
            break;

          case 'fullscreen-split-preview':
            this.prepareFullscreenPreviewMode();
            break;

          default: // Restore.
            this.prepareDefaultMode();
            break;
        }
      });
      this.$fullscreenSplitPreviewBtn.on('click' + this.ens, (e) => {
        e.stopImmediatePropagation(), e.preventDefault();

        switch (this.currentMode) {
          case 'default':
            this.prepareFullscreenSplitPreviewMode();
            break;

          case 'preview':
            this.prepareFullscreenSplitPreviewMode();
            break;

          case 'fullscreen':
            this.prepareFullscreenSplitPreviewMode();
            break;

          case 'fullscreen-preview':
            this.prepareFullscreenSplitPreviewMode();
            break;

          case 'fullscreen-split-preview':
            this.prepareFullscreenMode();
            break;

          default: // Restore.
            this.prepareDefaultMode();
            break;
        }
      });
      this.$fullscreenBtn.on('click' + this.ens, (e) => {
        e.stopImmediatePropagation(), e.preventDefault();

        switch (this.currentMode) {
          case 'default':
            this.prepareFullscreenMode();
            break;

          case 'preview':
            this.prepareFullscreenPreviewMode();
            break;

          case 'fullscreen':
            this.prepareDefaultMode();
            break;

          case 'fullscreen-preview':
            this.prepareDefaultMode();
            break;

          case 'fullscreen-split-preview':
            this.prepareDefaultMode();
            break;

          default: // Restore.
            this.prepareDefaultMode();
            break;
        }
      });
      this.$sitePreviewBtn.on('click' + this.ens, (e) => {
        e.stopImmediatePropagation(), e.preventDefault(), $sitePreviewTarget.click();
      });
      this.$saveDraftBtn.on('click' + this.ens, (e) => {
        e.stopImmediatePropagation(), e.preventDefault(), $saveDraftTarget.click();
      });
      this.$publishBtn.on('click' + this.ens, (e) => {
        e.stopImmediatePropagation(), e.preventDefault(), $publishTarget.click();
      });
      this.$updateBtn.on('click' + this.ens, (e) => {
        e.stopImmediatePropagation(), e.preventDefault(), $publishTarget.click();
      });
      this.$trashBtn.on('click' + this.ens, (e) => {
        e.stopImmediatePropagation(), e.preventDefault(), location.href = $trashTarget.attr('href');
      });
    }

    protected setupComplete() {
      this.$loading.fadeOut(200, () => {
        this.$loading.remove(),
          this.$loadingStyles.remove();

        delete this.initChain;
        delete this.setupChain;

        this.$toolbar.css({ visibility: '' }),
          this.$container.css({ visibility: '' }),
          this.$statusInfo.css({ visibility: '' });

        this.prepareDefaultMode(); // Let's rock.

        this.$area.trigger('setupComplete' + this.ens);
      });
    }

    /*
     * Document routines.
     */

    protected docValue(value?: string): string {
      if (value !== undefined) {

        if (this.ide) { // IDE?
          this.ide.setValue(value, -1);
        } else { // Default (textarea).
          this.$textarea.val(value);
        }

      } else { // Current value.

        if (this.ide) { // IDE?
          value = this.ide.getValue();
        } else { // Default (textarea).
          value = this.$textarea.val();
        }

      } // In either case, return normalized value.
      return $.trim(value!.replace(/(?:\r\n|\r)/g, '\n'));
    }

    /*
     * Body overflow routines.
     */

    protected fullscreenBodyOverflow(overflow?: boolean) {
      if (overflow === false) { // No overflow; and set width to avoid shifting content in body.
        this.$body.css({ overflow: 'hidden', width: 'calc(100% - ' + this.scrollbarWidth + 'px)' });
      } else { // Reset to a default behavior.
        this.$body.css({ overflow: '', width: '' });
      }
    }

    /*
     * Adjust container.
     */

    protected adjustContainer(e?: JQueryEventObject) {
      // NOTE: Do not resize container, only constrain as necessary.
      // i.e., The height, width, etc, should be handled via CSS.

      if (this.currentMode.indexOf('fullscreen') !== -1) {
        let toolbarHeight = this.$toolbar.outerHeight();
        this.$container.css({
          'max-height': 'calc(100% - ' + toolbarHeight + 'px)'
        });
      } else {
        this.$container.css({
          'max-height': '' // Remove.
        });
      } // And maybe handle IDE resize also.
      if (this.ide && (!e || e.type !== 'resize')) {
        this.ide.resize(true);
      } // Ace has its own resizer.
    }

    /*
     * Mode routines.
     */

    protected prepareDefaultMode() {
      this.$html.removeClass(this.previewClass),
        this.$html.removeClass(this.fullscreenClass),
        this.$html.removeClass(this.fullscreenPreviewClass),
        this.$html.removeClass(this.fullscreenSplitPreviewClass);

      this.$sitePreviewBtn.hide(),
        this.$saveDraftBtn.hide(),
        this.$publishBtn.hide(),
        this.$updateBtn.hide(),
        this.$trashBtn.hide(),
        this.$previewBtn.find('.-icon').removeClass('sharkicon-pencil').addClass('sharkicon-eye'),
        this.$fullscreenSplitPreviewBtn.find('.-icon').removeClass('sharkicon-pencil').addClass('sharkicon-columns'),
        this.$fullscreenBtn.find('.-icon').removeClass('sharkicon-octi-screen-normal').addClass('sharkicon-octi-screen-full');

      this.currentMode = 'default';

      this.fullscreenBodyOverflow(),
        this.adjustContainer();

      this.previewScrollSyncDisable();
      this.previewRender('');
    }

    protected preparePreviewMode() {
      this.$html.addClass(this.previewClass),
        this.$html.removeClass(this.fullscreenClass),
        this.$html.removeClass(this.fullscreenPreviewClass),
        this.$html.removeClass(this.fullscreenSplitPreviewClass);

      this.$sitePreviewBtn.hide(),
        this.$saveDraftBtn.hide(),
        this.$publishBtn.hide(),
        this.$updateBtn.hide(),
        this.$trashBtn.hide(),
        this.$previewBtn.find('.-icon').removeClass('sharkicon-eye').addClass('sharkicon-pencil'),
        this.$fullscreenSplitPreviewBtn.find('.-icon').removeClass('sharkicon-pencil').addClass('sharkicon-columns'),
        this.$fullscreenBtn.find('.-icon').removeClass('sharkicon-octi-screen-normal').addClass('sharkicon-octi-screen-full');

      this.currentMode = 'preview';

      this.fullscreenBodyOverflow(),
        this.adjustContainer();

      this.previewScrollSyncDisable();
      this.previewRender(this.docValue());
    }

    protected prepareFullscreenPreviewMode() {
      this.$html.removeClass(this.previewClass),
        this.$html.removeClass(this.fullscreenClass),
        this.$html.addClass(this.fullscreenPreviewClass),
        this.$html.removeClass(this.fullscreenSplitPreviewClass);

      this.$sitePreviewBtn.show(),
        this.$saveDraftBtn.show(),
        this.$publishBtn.show(),
        this.$updateBtn.show(),
        this.$trashBtn.show(),
        this.$previewBtn.find('.-icon').removeClass('sharkicon-eye').addClass('sharkicon-pencil'),
        this.$fullscreenSplitPreviewBtn.find('.-icon').removeClass('sharkicon-pencil').addClass('sharkicon-columns'),
        this.$fullscreenBtn.find('.-icon').removeClass('sharkicon-octi-screen-full').addClass('sharkicon-octi-screen-normal');

      this.currentMode = 'fullscreen-preview';

      this.fullscreenBodyOverflow(false),
        this.adjustContainer();

      this.previewScrollSyncDisable();
      this.previewRender(this.docValue());
    }

    protected prepareFullscreenSplitPreviewMode() {
      this.$html.removeClass(this.previewClass),
        this.$html.removeClass(this.fullscreenClass),
        this.$html.removeClass(this.fullscreenPreviewClass),
        this.$html.addClass(this.fullscreenSplitPreviewClass);

      this.$sitePreviewBtn.show(),
        this.$saveDraftBtn.show(),
        this.$publishBtn.show(),
        this.$updateBtn.show(),
        this.$trashBtn.show(),
        this.$previewBtn.find('.-icon').removeClass('sharkicon-pencil').addClass('sharkicon-eye'),
        this.$fullscreenSplitPreviewBtn.find('.-icon').removeClass('sharkicon-columns').addClass('sharkicon-pencil'),
        this.$fullscreenBtn.find('.-icon').removeClass('sharkicon-octi-screen-full').addClass('sharkicon-octi-screen-normal');

      this.currentMode = 'fullscreen-split-preview';

      this.fullscreenBodyOverflow(false),
        this.adjustContainer();

      this.previewScrollSyncEnable();
      this.previewRender(this.docValue());
    }

    protected prepareFullscreenMode() {
      this.$html.removeClass(this.previewClass),
        this.$html.addClass(this.fullscreenClass),
        this.$html.removeClass(this.fullscreenPreviewClass),
        this.$html.removeClass(this.fullscreenSplitPreviewClass);

      this.$sitePreviewBtn.show(),
        this.$saveDraftBtn.show(),
        this.$publishBtn.show(),
        this.$updateBtn.show(),
        this.$trashBtn.show(),
        this.$previewBtn.find('.-icon').removeClass('sharkicon-pencil').addClass('sharkicon-eye'),
        this.$fullscreenSplitPreviewBtn.find('.-icon').removeClass('sharkicon-pencil').addClass('sharkicon-columns'),
        this.$fullscreenBtn.find('.-icon').removeClass('sharkicon-octi-screen-full').addClass('sharkicon-octi-screen-normal');

      this.currentMode = 'fullscreen';

      this.fullscreenBodyOverflow(false),
        this.adjustContainer();

      this.previewScrollSyncDisable();
      this.previewRender('');
    }

    /*
     * Markdown routines.
     */

    protected previewRender(md?: string) {
      if (md === '') // Explicitly.
        return this.$previewDiv.html('');

      // Otherwise, only if visible.
      if (!this.$preview.is(':visible'))
        return; // Not applicable.

      // If not passed in, detect automatically.
      if (md === undefined) md = this.docValue();

      if (!md) // Nothing to preview.
        return this.$previewDiv.html('');

      if (this.data.settings.previewMethod === 'php') {
        if (this.previewXhr) {
          this.previewXhr.abort();
        }
        this.previewXhr = $.ajax({
          type: 'POST',

          processData: false,
          data: pako.deflate(md),

          headers: { 'content-encoding': 'gzip' },
          contentType: 'application/octet-stream',
          url: this.data.settings.ajaxRestActionPreviewUrl,

          success: (r) => {
            let html = r.html; // Response.
            let $div = $('<div>' + html + '</div>');

            this.hljsNode($div);
            this.$previewDiv.html($div.html());
          },
          error: (e) => this.log('error', e)
        });
      } else {
        let mdIt = markdownit({
          html: true,
          xhtmlOut: true,
          breaks: true,

          linkify: false,
          // Do not auto linkify URLs.
          // This is off by default in PHP also.

          quotes: '“”‘’',
          typographer: true,
          // This is on for quotes/dashes only.
          // Note: This typographer setting transforms things like (sm) (tm) into symbols too.
          // Nice, but SmartyPants in PHP does not do this.

          langPrefix: 'lang-',
          highlight: this.hljsCode,

          // Now add extensions.
        }).use(markdownItAttrs)
          .use(markdownitDeflist)
          .use(markdownitAbbr)
          .use(markdownitFootnote);

        mdIt.renderer.rules.heading_open = function (tokens: any, idx: number, options: any, env: any, slf: any) {
          let raw = tokens[ idx + 1 ].children.reduce((a: string, t: any) => a + t.content, '');

          let id = raw.toLowerCase();
          id = id.replace(/[^\w]+/g, '-');
          id = 'j2h.' + id.replace(/(?:^[\s\-]+|[\s\-]+$)/g, '');

          tokens[ idx ].attrs = tokens[ idx ].attrs || [],
            tokens[ idx ].attrs.push([ 'id', id ]);

          return slf.renderToken.apply(slf, arguments);
        };
        let html = mdIt.render(md);
        this.$previewDiv.html(html);
      }
    }

    protected hljsNode($node: JQuery) {
      let exclusions = '.no-hljs, .no-highlight, .nohighlight',
        plainText = '.lang-none, .lang-plain, .lang-text, .lang-txt, .none, .plain, .text, .txt';

      $node.find('pre > code').not(exclusions).each((i, obj) => {
        let $obj = $(obj), $parent = $obj.parent();
        $parent.addClass('hljs-pre');

        if ($obj.is(plainText)) {
          $obj.addClass('hljs lang-none');
        } else {
          hljs.highlightBlock(obj);
        }
      }); // All `pre > code` in the node.
    }

    protected hljsCode(code: string, lang: string) {
      if (code && lang && $.inArray(lang, [ 'none', 'plain', 'text', 'txt' ]) === -1) {
        code = hljs.highlightAuto(code, lang ? [ lang ] : undefined).value;
      }
      return '<pre class="hljs-pre">' +
        '<code class="hljs ' + _.escape(lang || 'none') + '">' +
        code + '</code>' +
        '</pre>';
    }

    /*
     * Scroll-lock routines.
     */

    protected scrollLockingEnable() {
      this.scrollLockingDisable();

      if (this.ide) { // IDE?
        let $sb = this.$ide.find('> .ace_scrollbar-v'),
          $ct = this.$ide.find('> .ace_scroller > .ace_content');

        this.scrollLockHandler = (e) => {
          let deltaY = e.originalEvent.deltaY,
            direction = deltaY < 0 ? 'up' : 'down',
            scrollTop = $sb.scrollTop();

          if ( // If up to the top, or down to the bottom.
            (direction === 'up' && scrollTop + deltaY <= 0) ||
            (direction === 'down' && scrollTop + deltaY >= $sb.prop('scrollHeight') - $sb.innerHeight())
          ) {
            e.preventDefault(), e.stopImmediatePropagation();
            this.ide.session.setScrollTop(scrollTop + deltaY);
          }
        };
        $ct.on('wheel' + this.ens + '-scroll-lock', this.scrollLockHandler);

      } else { // Default (textarea).
        let $ta = this.$textarea;

        this.scrollLockHandler = (e) => {
          let deltaY = e.originalEvent.deltaY,
            direction = deltaY < 0 ? 'up' : 'down',
            scrollTop = $ta.scrollTop();

          if ( // If up to the top, or down to the bottom.
            (direction === 'up' && scrollTop + deltaY <= 0) ||
            (direction === 'down' && scrollTop + deltaY >= $ta.prop('scrollHeight') - $ta.innerHeight())
          ) {
            e.preventDefault(), e.stopImmediatePropagation();
            $ta.scrollTop(scrollTop + deltaY);
          }
        };
        $ta.on('wheel' + this.ens + '-scroll-lock', this.scrollLockHandler);
      }
    }

    protected scrollLockingDisable() {
      if (!this.scrollLockHandler)
        return; // Nothing to do.

      if (this.ide) { // IDE enabled?
        let $ct = this.$ide.find('> .ace_scroller > .ace_content');
        $ct.off('wheel' + this.ens + '-scroll-lock', this.scrollLockHandler);
      } else { // Default (i.e., textarea).
        this.$textarea.off('wheel' + this.ens + '-scroll-lock', this.scrollLockHandler);
      }
    }

    /*
     * Scroll-sync routines.
     */

    protected previewScrollSyncEnable() {
      this.previewScrollSyncDisable();
      this.$previewBody.scrollTop(0);

      if (this.ide) { // IDE?
        this.ide.session.setScrollTop(0);

        let $pw = this.$previewWindow, $pb = this.$previewBody,
          $sb = this.$ide.find('> .ace_scrollbar-v');

        this.previewScrollSyncHandler = (() => {
          let percentage = $sb.scrollTop() / ($sb.prop('scrollHeight') - $sb.outerHeight());
          $pw[ 0 ].scrollTo(0, Math.round(percentage * ($pb.prop('scrollHeight') - $pb.outerHeight())));
        }).bind(this); // Bind callback handler.

        this.ide.session.on('changeScrollTop', this.previewScrollSyncHandler);

      } else { // Default (textarea).
        this.$textarea.scrollTop(0);

        let $pw = this.$previewWindow, $pb = this.$previewBody,
          $ta = this.$textarea; // Just a simple textarea.

        this.previewScrollSyncHandler = (() => {
          let percentage = $ta.scrollTop() / ($ta.prop('scrollHeight') - $ta.outerHeight());
          $pw[ 0 ].scrollTo(0, Math.round(percentage * ($pb.prop('scrollHeight') - $pb.outerHeight())));
        }).bind(this); // Bind callback handler.

        this.$textarea.on('scroll' + this.ens + this.ens + '-preview-scroll-sync', this.previewScrollSyncHandler);
      }
    }

    protected previewScrollSyncDisable() {
      if (!this.previewScrollSyncHandler)
        return;  // Nothing to do.

      if (this.ide) { // IDE enabled?
        this.ide.session.removeListener('changeScrollTop', this.previewScrollSyncHandler);
      } else { // Default (i.e., textarea).
        this.$textarea.off('scroll' + this.ens + this.ens + '-preview-scroll-sync', this.previewScrollSyncHandler);
      }
    }

    /*
     * Scroll-expand routines.
     */

    protected automaticScrollExpand(e: JQueryEventObject) {
      if (this.ide) return; // Not applicable.

      if (e.which !== 13) return; // Not enter key.
      if (e.shiftKey || e.ctrlKey || e.altKey || e.metaKey) return;

      let start = this.$textarea.prop('selectionStart'),
        end = this.$textarea.prop('selectionEnd');

      if (start === end && start >= $.trim(this.$textarea.val()).length)
        this.$textarea.scrollTop(this.$textarea.prop('scrollHeight'));
    }

    /*
     * Scrollbar width property.
     */

    protected get scrollbarWidth(): number {
      if (this._scrollbarWidth !== undefined) {
        return this._scrollbarWidth;
      } // Already determined this.

      let $div = $('<div></div>');

      $div.css({ // Scrollbar width.
        overflow: 'scroll',
        width: '100px', height: '100px',
        position: 'absolute', top: '-9999px',
      });
      this.$body.append($div); // Add to DOM temporarily.
      this._scrollbarWidth = $div.outerWidth() - $div.prop('clientWidth');
      $div.remove(); // We can cleanup temporary div now.

      return this._scrollbarWidth;
    }

    /*
     * Debug routines.
     */

    public log(...log: any[]) {
      $.each(log, (i, data) => console.log(this.cns + ': %o', data));
    }
  }
  export var WpMarkdownExtraEditorInstance = new Editor();
}
