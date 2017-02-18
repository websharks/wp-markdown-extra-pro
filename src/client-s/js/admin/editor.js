"use strict";
var WpMarkdownExtraEditor;
(function (WpMarkdownExtraEditor) {
    ;
})(WpMarkdownExtraEditor || (WpMarkdownExtraEditor = {}));
"use strict";
var WpMarkdownExtraEditor;
(function (WpMarkdownExtraEditor) {
    var $ = jQuery;
    var MediaHtml = (function () {
        function MediaHtml(ed) {
            this.ed = ed;
        }
        MediaHtml.prototype.format = function (html) {
            var _this = this;
            if (/\[gallery\s/i.test(html)) {
                return html;
            }
            else if (!/<img\s.*?src=['"]([^'"]+)/i.test(html)) {
                return html;
            }
            var r = '';
            $.each(html.split(/[\r\n]+/), function (i, html) {
                var src, href, alt, cls, align, width;
                if (!(src = /<img\s.*?src=['"]([^'"]+)/i.exec(html))) {
                    return;
                }
                href = /<a\s.*?href=['"]([^'"]+)/i.exec(html);
                alt = /<img\s.*?alt=['"]([^'"]+)/i.exec(html);
                cls = /<img\s.*?class=['"]([^'"]+)/i.exec(html);
                align = /\[caption\s.*?align=['"]([^'"]+)/i.exec(html);
                width = /(?:<img|\[caption)\s.*?width=['"]([^'"]+)/i.exec(html);
                if (href) {
                    href[1] = href[1].replace(/^https?:\/{2}/i, '//');
                }
                src[1] = src[1].replace(/^https?:\/{2}/i, '//');
                cls = !cls && align ? align : cls;
                if (cls && /alignnone/i.test(cls[1])) {
                    cls = null;
                }
                r += '\n';
                if (_this.ed.data.settings.mediaInserts === 'html') {
                    if (href) {
                        r += '<a href="' + _.escape(href[1]) + '">';
                    }
                    r += '<img src="' + _.escape(src[1]) + '"';
                    if (alt) {
                        r += ' alt="' + _.escape(alt[1]) + '"';
                    }
                    if (cls && /alignleft/i.test(cls[1])) {
                        r += ' class="alignleft"';
                    }
                    else if (cls && /aligncenter/i.test(cls[1])) {
                        r += ' class="aligncenter"';
                    }
                    else if (cls && /alignright/i.test(cls[1])) {
                        r += ' class="alignright"';
                    }
                    if (width) {
                        r += ' width="' + _.escape(width[1]) + '"';
                    }
                    r += ' />';
                    if (href) {
                        r += '</a>';
                    }
                }
                else {
                    if (href) {
                        r += '[';
                    }
                    r += '![';
                    if (alt) {
                        r += alt[1]
                            .replace(/\[/g, '(')
                            .replace(/\]/g, ')');
                    }
                    r += '](';
                    r += src[1]
                        .replace(/\(/g, '[')
                        .replace(/\)/g, ']');
                    r += ')';
                    if (cls || width) {
                        r += '{';
                    }
                    if (cls) {
                        if (/alignleft/i.test(cls[1])) {
                            r += '.alignleft';
                        }
                        else if (/aligncenter/i.test(cls[1])) {
                            r += '.aligncenter';
                        }
                        else if (/alignright/i.test(cls[1])) {
                            r += '.alignright';
                        }
                    }
                    if (cls && width) {
                        r += ' width=' + _.escape(width[1]);
                    }
                    else if (width) {
                        r += 'width=' + _.escape(width[1]);
                    }
                    if (cls || width) {
                        r += '}';
                    }
                    if (href) {
                        r += '](';
                        r += href[1]
                            .replace(/\(/g, '[')
                            .replace(/\)/g, ']');
                        r += ')';
                    }
                }
            });
            return r + '\n';
        };
        return MediaHtml;
    }());
    WpMarkdownExtraEditor.MediaHtml = MediaHtml;
})(WpMarkdownExtraEditor || (WpMarkdownExtraEditor = {}));
"use strict";
var WpMarkdownExtraEditor;
(function (WpMarkdownExtraEditor) {
    var $ = jQuery;
    var Editor = (function () {
        function Editor() {
            var _this = this;
            this.data = sxz4aq7w68twt86g8ye5m3np7nrtguw8EditorData;
            this.cns = this.data.brand.slug + '-editor',
                this.ens = '.' + this.data.brand.slug + '-editor';
            this.mediaHtml = new WpMarkdownExtraEditor.MediaHtml(this);
            this.initChain = $.Deferred().resolve().promise(),
                this.setupChain = $.Deferred().resolve().promise();
            $(document).ready(function () { return _this.onDomReady(); });
        }
        Editor.prototype.onDomReady = function () {
            this.$textarea = $('.' + this.cns + '-textarea');
            if (this.$textarea.length)
                this.init();
        };
        Editor.prototype.init = function () {
            var _this = this;
            this.initChain
                .then(function () { return _this.initClasses(); })
                .then(function () { return _this.initElements(); })
                .then(function () { return _this.initIde(); })
                .then(function () { return _this.initPreview(); })
                .then(function () { return _this.initComplete(); })
                .then(function () { return _this.setup(); });
        };
        Editor.prototype.initClasses = function () {
            this.themeClass = this.cns + '-' + this.data.settings.theme + '-theme';
            this.previewClass = this.cns + '-preview-mode';
            this.fullscreenClass = this.cns + '-fullscreen-mode';
            this.fullscreenPreviewClass = this.cns + '-fullscreen-preview-mode';
            this.fullscreenSplitPreviewClass = this.cns + '-fullscreen-split-preview-mode';
        };
        Editor.prototype.initElements = function () {
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
            if (this.data.settings.ideEnable) {
                this.$ide = $('<textarea class="' + _.escape(this.cns + '-ide') + '"></textarea>'),
                    this.$textarea.before(this.$ide);
            }
            this.$statusInfo = this.$area.find('#post-status-info'),
                this.$statusInfo.css({ visibility: 'hidden' }),
                this.$statusInfo.addClass(this.cns + '-status-info');
            this.$textareaResizeHandle = this.$area.find('#content-resize-handle'),
                this.$textareaResizeHandle.hide();
            this.$preview = $('<iframe class="' + _.escape(this.cns + '-preview') + '" src="' + _.escape(this.data.settings.previewUrl) + '"></iframe>');
            this.$loadingStyles = this.$area.find('.' + this.cns + '-loading-styles'),
                this.$loading = this.$area.find('.' + this.cns + '-loading');
        };
        Editor.prototype.initIde = function () {
            if (!this.data.settings.ideEnable) {
                return;
            }
            this.ide = ace.edit(this.$ide[0]);
            if (this.data.settings.ide.maxLines === 'Infinity') {
                this.data.settings.ide.maxLines = Infinity;
            }
            this.ide.setOptions(this.data.settings.ide);
            this.ide.setStyle(this.cns + '-ide');
            this.ide.$blockScrolling = Infinity;
            this.docValue(this.$textarea.val());
            this.$textarea.addClass('-position-offscreen');
            this.$ide = this.$container.find('.' + this.cns + '-ide');
        };
        Editor.prototype.initPreview = function () {
            var _this = this;
            var deferred = $.Deferred(function (deferred) {
                _this.$preview.on('load' + _this.ens, function () {
                    var iframe = _this.$preview[0];
                    _this.$previewWindow = $(iframe.contentWindow || document.defaultView);
                    _this.$previewDocument = $(iframe.contentDocument || iframe.contentWindow.document);
                    _this.$previewBody = _this.$previewDocument.find('body');
                    _this.$previewDiv = _this.$previewBody.find('#___div');
                    var $body = _this.$previewBody;
                    if (_this.data.settings.hljsStyleUrl) {
                        var href = _this.data.settings.hljsStyleUrl, integrity = ' integrity="' + _.escape(_this.data.settings.hljsStyleSri) + '" crossorigin="anonymous"';
                        $body.append('<link type="text/css" rel="stylesheet" href="' + _.escape(href) + '"' + integrity + ' />');
                    }
                    if (_this.data.settings.previewStylesUrl) {
                        var href = _this.data.settings.previewStylesUrl, integrity = ' integrity="' + _.escape(_this.data.settings.previewStylesSri) + '" crossorigin="anonymous"';
                        $body.append('<link type="text/css" rel="stylesheet" href="' + _.escape(href) + '"' + integrity + ' />');
                    }
                    if (_this.data.settings.hljsBgColor) {
                        var background = _this.data.settings.hljsBgColor;
                        $body.append('<style>.hljs-pre > .hljs { background: ' + background.replace(/[<&>]/g, '') + ' !important; }</style>');
                    }
                    if (_this.data.settings.hljsFontFamily) {
                        var fontFamily = _this.data.settings.hljsFontFamily;
                        $body.append('<style>.hljs-pre > .hljs { font-family: ' + fontFamily.replace(/[<&>]/g, '') + ' !important; }</style>');
                    }
                    if (_this.data.settings.customPreviewStyles) {
                        var customPreviewStyles = _this.data.settings.customPreviewStyles;
                        $body.append('<style>' + customPreviewStyles + '</style>');
                    }
                    deferred.resolve();
                });
                _this.$container.append(_this.$preview);
            });
            return deferred.promise();
        };
        Editor.prototype.initComplete = function () {
            this.$area.trigger('initComplete' + this.ens);
        };
        Editor.prototype.setup = function () {
            var _this = this;
            this.setupChain
                .then(function () { return _this.setupTextarea(); })
                .then(function () { return _this.setupIde(); })
                .then(function () { return _this.setupContainer(); })
                .then(function () { return _this.setupMediaBtn(); })
                .then(function () { return _this.setupToolbar(); })
                .then(function () { return _this.setupComplete(); });
        };
        Editor.prototype.setupTextarea = function () {
            var _this = this;
            if (this.data.settings.ideEnable) {
                return;
            }
            this.scrollLockingEnable();
            this.$textarea.css({
                'font-size': this.data.settings.fontSize,
                'font-family': this.data.settings.fontFamily,
            });
            var previewDelay = this.data.settings.previewMethod === 'php' ? 1000 : 250, previewDebounce = _.debounce(function () { return _this.previewRender(); }, previewDelay);
            this.$textarea.on('keyup' + this.ens, function (e) { _this.automaticScrollExpand(e); });
            this.$textarea.on('cut' + this.ens + ' paste' + this.ens + ' keyup' + this.ens + ' change' + this.ens, previewDebounce);
        };
        Editor.prototype.setupIde = function () {
            var _this = this;
            if (!this.data.settings.ideEnable) {
                return;
            }
            this.scrollLockingEnable();
            var updateDebounce = _.debounce(function () {
                _this.$textarea.val(_this.docValue());
                _this.$textarea.trigger('input', 'ide-update' + _this.ens);
            }, 1000);
            var previewDelay = this.data.settings.previewMethod === 'php' ? 1000 : 250, previewDebounce = _.debounce(function () { return _this.previewRender(); }, previewDelay);
            this.ide.session.on('change', updateDebounce),
                this.ide.session.on('change', previewDebounce);
            this.$textarea.on('input' + this.ens, function (e, via) {
                if (via === 'ide-update' + _this.ens)
                    return;
                _this.ide.focus(), _this.docValue(_this.$textarea.val());
            });
            $('#post-preview').on('click' + this.ens, function () { return _this.$textarea.val(_this.docValue()); }),
                this.$form.on('submit' + this.ens, function () { return _this.$textarea.val(_this.docValue()); });
        };
        Editor.prototype.setupContainer = function () {
            var _this = this;
            var delay = 250, debounce = _.debounce(function (e) { return _this.adjustContainer(e); }, delay);
            $(window).on('resize' + this.ens, debounce);
        };
        Editor.prototype.setupMediaBtn = function () {
            var _this = this;
            var sendToEditor = window.send_to_editor;
            window.send_to_editor = function (html) {
                html = _this.mediaHtml.format(html);
                if (_this.ide) {
                    _this.ide.insert(html);
                }
                else
                    sendToEditor(html);
            };
        };
        Editor.prototype.setupToolbar = function () {
            var _this = this;
            this.$previewBtn = $('<button type="button" class="-preview button" title="' + _.escape(this.data.i18n.preview + ' ' + this.data.i18n.toggle) + '"><span class="-icon sharkicon sharkicon-eye"></span></button>');
            this.$fullscreenSplitPreviewBtn = $('<button type="button" class="-fullscreen-split button" title="' + _.escape(this.data.i18n.fullscreenSplitPreview + ' ' + this.data.i18n.toggle) + '"><span class="-icon sharkicon sharkicon-columns"></span></button>');
            this.$sitePreviewBtn = $('<button type="button" class="-post-preview button" title="' + _.escape(this.data.i18n.sitePreview) + '"><span class="-icon sharkicon sharkicon-octi-link-external"></span></button>');
            this.$fullscreenBtn = $('<button type="button" class="-fullscreen button" title="' + _.escape(this.data.i18n.fullscreen + ' ' + this.data.i18n.toggle) + '"><span class="-icon sharkicon sharkicon-octi-screen-full"></span></button>');
            this.$saveDraftBtn = $('<button type="button" class="-save-draft button" title="' + _.escape(this.data.i18n.saveDraft) + '"><span class="-icon sharkicon sharkicon-floppy-o"></span></button>');
            this.$publishBtn = $('<button type="button" class="-publish button" title="' + _.escape(this.data.i18n.publish) + '"><span class="-icon sharkicon sharkicon-thumb-tack"></span></button>');
            this.$updateBtn = $('<button type="button" class="-update button" title="' + _.escape(this.data.i18n.update) + '"><span class="-icon sharkicon sharkicon-thumb-tack"></span></button>');
            this.$trashBtn = $('<button type="button" class="-trash button" title="' + _.escape(this.data.i18n.trash) + '"><span class="-icon sharkicon sharkicon-trash"></span></button>');
            var $sitePreviewTarget = $('#preview-action:visible > #post-preview:visible'), $saveDraftTarget = $('#save-action:visible > #save-post:visible'), $publishTarget = $('#publishing-action:visible > #publish:visible'), $trashTarget = $('#delete-action:visible > a:visible');
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
                }
                else {
                    this.$toolbarBtns.append(this.$publishBtn);
                }
            }
            if ($trashTarget.length) {
                this.$toolbarBtns.append(this.$trashBtn);
            }
            this.$toolbarBtns.append(this.$fullscreenBtn);
            this.$previewBtn.on('click' + this.ens, function (e) {
                e.stopImmediatePropagation(), e.preventDefault();
                switch (_this.currentMode) {
                    case 'default':
                        _this.preparePreviewMode();
                        break;
                    case 'preview':
                        _this.prepareDefaultMode();
                        break;
                    case 'fullscreen':
                        _this.prepareFullscreenPreviewMode();
                        break;
                    case 'fullscreen-preview':
                        _this.prepareFullscreenMode();
                        break;
                    case 'fullscreen-split-preview':
                        _this.prepareFullscreenPreviewMode();
                        break;
                    default:
                        _this.prepareDefaultMode();
                        break;
                }
            });
            this.$fullscreenSplitPreviewBtn.on('click' + this.ens, function (e) {
                e.stopImmediatePropagation(), e.preventDefault();
                switch (_this.currentMode) {
                    case 'default':
                        _this.prepareFullscreenSplitPreviewMode();
                        break;
                    case 'preview':
                        _this.prepareFullscreenSplitPreviewMode();
                        break;
                    case 'fullscreen':
                        _this.prepareFullscreenSplitPreviewMode();
                        break;
                    case 'fullscreen-preview':
                        _this.prepareFullscreenSplitPreviewMode();
                        break;
                    case 'fullscreen-split-preview':
                        _this.prepareFullscreenMode();
                        break;
                    default:
                        _this.prepareDefaultMode();
                        break;
                }
            });
            this.$fullscreenBtn.on('click' + this.ens, function (e) {
                e.stopImmediatePropagation(), e.preventDefault();
                switch (_this.currentMode) {
                    case 'default':
                        _this.prepareFullscreenMode();
                        break;
                    case 'preview':
                        _this.prepareFullscreenPreviewMode();
                        break;
                    case 'fullscreen':
                        _this.prepareDefaultMode();
                        break;
                    case 'fullscreen-preview':
                        _this.prepareDefaultMode();
                        break;
                    case 'fullscreen-split-preview':
                        _this.prepareDefaultMode();
                        break;
                    default:
                        _this.prepareDefaultMode();
                        break;
                }
            });
            this.$sitePreviewBtn.on('click' + this.ens, function (e) {
                e.stopImmediatePropagation(), e.preventDefault(), $sitePreviewTarget.click();
            });
            this.$saveDraftBtn.on('click' + this.ens, function (e) {
                e.stopImmediatePropagation(), e.preventDefault(), $saveDraftTarget.click();
            });
            this.$publishBtn.on('click' + this.ens, function (e) {
                e.stopImmediatePropagation(), e.preventDefault(), $publishTarget.click();
            });
            this.$updateBtn.on('click' + this.ens, function (e) {
                e.stopImmediatePropagation(), e.preventDefault(), $publishTarget.click();
            });
            this.$trashBtn.on('click' + this.ens, function (e) {
                e.stopImmediatePropagation(), e.preventDefault(), location.href = $trashTarget.attr('href');
            });
        };
        Editor.prototype.setupComplete = function () {
            var _this = this;
            this.$loading.fadeOut(200, function () {
                _this.$loading.remove(),
                    _this.$loadingStyles.remove();
                delete _this.initChain;
                delete _this.setupChain;
                _this.$toolbar.css({ visibility: '' }),
                    _this.$container.css({ visibility: '' }),
                    _this.$statusInfo.css({ visibility: '' });
                _this.prepareDefaultMode();
                _this.$area.trigger('setupComplete' + _this.ens);
            });
        };
        Editor.prototype.docValue = function (value) {
            if (value !== undefined) {
                if (this.ide) {
                    this.ide.setValue(value, -1);
                }
                else {
                    this.$textarea.val(value);
                }
            }
            else {
                if (this.ide) {
                    value = this.ide.getValue();
                }
                else {
                    value = this.$textarea.val();
                }
            }
            return $.trim(value.replace(/(?:\r\n|\r)/g, '\n'));
        };
        Editor.prototype.fullscreenBodyOverflow = function (overflow) {
            if (overflow === false) {
                this.$body.css({ overflow: 'hidden', width: 'calc(100% - ' + this.scrollbarWidth + 'px)' });
            }
            else {
                this.$body.css({ overflow: '', width: '' });
            }
        };
        Editor.prototype.adjustContainer = function (e) {
            if (this.currentMode.indexOf('fullscreen') !== -1) {
                var toolbarHeight = this.$toolbar.outerHeight();
                this.$container.css({
                    'max-height': 'calc(100% - ' + toolbarHeight + 'px)'
                });
            }
            else {
                this.$container.css({
                    'max-height': ''
                });
            }
            if (this.ide && (!e || e.type !== 'resize')) {
                this.ide.resize(true);
            }
        };
        Editor.prototype.prepareDefaultMode = function () {
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
        };
        Editor.prototype.preparePreviewMode = function () {
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
        };
        Editor.prototype.prepareFullscreenPreviewMode = function () {
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
        };
        Editor.prototype.prepareFullscreenSplitPreviewMode = function () {
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
        };
        Editor.prototype.prepareFullscreenMode = function () {
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
        };
        Editor.prototype.previewRender = function (md) {
            var _this = this;
            if (md === '')
                return this.$previewDiv.html('');
            if (!this.$preview.is(':visible'))
                return;
            if (md === undefined)
                md = this.docValue();
            if (!md)
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
                    success: function (r) {
                        var html = r.html;
                        var $div = $('<div>' + html + '</div>');
                        _this.hljsNode($div);
                        _this.$previewDiv.html($div.html());
                    },
                    error: function (e) { return _this.log('error', e); }
                });
            }
            else {
                var mdIt = markdownit({
                    html: true,
                    xhtmlOut: true,
                    breaks: true,
                    linkify: false,
                    quotes: '“”‘’',
                    typographer: true,
                    langPrefix: 'lang-',
                    highlight: this.hljsCode,
                }).use(markdownItAttrs)
                    .use(markdownitDeflist)
                    .use(markdownitAbbr)
                    .use(markdownitFootnote);
                mdIt.renderer.rules.heading_open = function (tokens, idx, options, env, slf) {
                    var raw = tokens[idx + 1].children.reduce(function (a, t) { return a + t.content; }, '');
                    var id = raw.toLowerCase();
                    id = id.replace(/[^\w]+/g, '-');
                    id = 'j2h.' + id.replace(/(?:^[\s\-]+|[\s\-]+$)/g, '');
                    tokens[idx].attrs = tokens[idx].attrs || [],
                        tokens[idx].attrs.push(['id', id]);
                    return slf.renderToken.apply(slf, arguments);
                };
                var html = mdIt.render(md);
                this.$previewDiv.html(html);
            }
        };
        Editor.prototype.hljsNode = function ($node) {
            var exclusions = '.no-hljs, .no-highlight, .nohighlight', plainText = '.lang-none, .lang-plain, .lang-text, .lang-txt, .none, .plain, .text, .txt';
            $node.find('pre > code').not(exclusions).each(function (i, obj) {
                var $obj = $(obj), $parent = $obj.parent();
                $parent.addClass('hljs-pre');
                if ($obj.is(plainText)) {
                    $obj.addClass('hljs lang-none');
                }
                else {
                    hljs.highlightBlock(obj);
                }
            });
        };
        Editor.prototype.hljsCode = function (code, lang) {
            if (code && lang && $.inArray(lang, ['none', 'plain', 'text', 'txt']) === -1) {
                code = hljs.highlightAuto(code, lang ? [lang] : undefined).value;
            }
            return '<pre class="hljs-pre">' +
                '<code class="hljs ' + _.escape(lang || 'none') + '">' +
                code + '</code>' +
                '</pre>';
        };
        Editor.prototype.scrollLockingEnable = function () {
            var _this = this;
            this.scrollLockingDisable();
            if (this.ide) {
                var $sb_1 = this.$ide.find('> .ace_scrollbar-v'), $ct = this.$ide.find('> .ace_scroller > .ace_content');
                this.scrollLockHandler = function (e) {
                    var deltaY = e.originalEvent.deltaY, direction = deltaY < 0 ? 'up' : 'down', scrollTop = $sb_1.scrollTop();
                    if ((direction === 'up' && scrollTop + deltaY <= 0) ||
                        (direction === 'down' && scrollTop + deltaY >= $sb_1.prop('scrollHeight') - $sb_1.innerHeight())) {
                        e.preventDefault(), e.stopImmediatePropagation();
                        _this.ide.session.setScrollTop(scrollTop + deltaY);
                    }
                };
                $ct.on('wheel' + this.ens + '-scroll-lock', this.scrollLockHandler);
            }
            else {
                var $ta_1 = this.$textarea;
                this.scrollLockHandler = function (e) {
                    var deltaY = e.originalEvent.deltaY, direction = deltaY < 0 ? 'up' : 'down', scrollTop = $ta_1.scrollTop();
                    if ((direction === 'up' && scrollTop + deltaY <= 0) ||
                        (direction === 'down' && scrollTop + deltaY >= $ta_1.prop('scrollHeight') - $ta_1.innerHeight())) {
                        e.preventDefault(), e.stopImmediatePropagation();
                        $ta_1.scrollTop(scrollTop + deltaY);
                    }
                };
                $ta_1.on('wheel' + this.ens + '-scroll-lock', this.scrollLockHandler);
            }
        };
        Editor.prototype.scrollLockingDisable = function () {
            if (!this.scrollLockHandler)
                return;
            if (this.ide) {
                var $ct = this.$ide.find('> .ace_scroller > .ace_content');
                $ct.off('wheel' + this.ens + '-scroll-lock', this.scrollLockHandler);
            }
            else {
                this.$textarea.off('wheel' + this.ens + '-scroll-lock', this.scrollLockHandler);
            }
        };
        Editor.prototype.previewScrollSyncEnable = function () {
            this.previewScrollSyncDisable();
            this.$previewBody.scrollTop(0);
            if (this.ide) {
                this.ide.session.setScrollTop(0);
                var $pw_1 = this.$previewWindow, $pb_1 = this.$previewBody, $sb_2 = this.$ide.find('> .ace_scrollbar-v');
                this.previewScrollSyncHandler = (function () {
                    var percentage = $sb_2.scrollTop() / ($sb_2.prop('scrollHeight') - $sb_2.outerHeight());
                    $pw_1[0].scrollTo(0, Math.round(percentage * ($pb_1.prop('scrollHeight') - $pb_1.outerHeight())));
                }).bind(this);
                this.ide.session.on('changeScrollTop', this.previewScrollSyncHandler);
            }
            else {
                this.$textarea.scrollTop(0);
                var $pw_2 = this.$previewWindow, $pb_2 = this.$previewBody, $ta_2 = this.$textarea;
                this.previewScrollSyncHandler = (function () {
                    var percentage = $ta_2.scrollTop() / ($ta_2.prop('scrollHeight') - $ta_2.outerHeight());
                    $pw_2[0].scrollTo(0, Math.round(percentage * ($pb_2.prop('scrollHeight') - $pb_2.outerHeight())));
                }).bind(this);
                this.$textarea.on('scroll' + this.ens + this.ens + '-preview-scroll-sync', this.previewScrollSyncHandler);
            }
        };
        Editor.prototype.previewScrollSyncDisable = function () {
            if (!this.previewScrollSyncHandler)
                return;
            if (this.ide) {
                this.ide.session.removeListener('changeScrollTop', this.previewScrollSyncHandler);
            }
            else {
                this.$textarea.off('scroll' + this.ens + this.ens + '-preview-scroll-sync', this.previewScrollSyncHandler);
            }
        };
        Editor.prototype.automaticScrollExpand = function (e) {
            if (this.ide)
                return;
            if (e.which !== 13)
                return;
            if (e.shiftKey || e.ctrlKey || e.altKey || e.metaKey)
                return;
            var start = this.$textarea.prop('selectionStart'), end = this.$textarea.prop('selectionEnd');
            if (start === end && start >= $.trim(this.$textarea.val()).length)
                this.$textarea.scrollTop(this.$textarea.prop('scrollHeight'));
        };
        Object.defineProperty(Editor.prototype, "scrollbarWidth", {
            get: function () {
                if (this._scrollbarWidth !== undefined) {
                    return this._scrollbarWidth;
                }
                var $div = $('<div></div>');
                $div.css({
                    overflow: 'scroll',
                    width: '100px', height: '100px',
                    position: 'absolute', top: '-9999px',
                });
                this.$body.append($div);
                this._scrollbarWidth = $div.outerWidth() - $div.prop('clientWidth');
                $div.remove();
                return this._scrollbarWidth;
            },
            enumerable: true,
            configurable: true
        });
        Editor.prototype.log = function () {
            var _this = this;
            var log = [];
            for (var _i = 0; _i < arguments.length; _i++) {
                log[_i] = arguments[_i];
            }
            $.each(log, function (i, data) { return console.log(_this.cns + ': %o', data); });
        };
        return Editor;
    }());
    WpMarkdownExtraEditor.Editor = Editor;
    WpMarkdownExtraEditor.WpMarkdownExtraEditorInstance = new Editor();
})(WpMarkdownExtraEditor || (WpMarkdownExtraEditor = {}));
