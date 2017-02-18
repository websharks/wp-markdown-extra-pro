namespace WpMarkdownExtraEditor {
  const $: JQueryStatic = jQuery;

  export class MediaHtml {

    protected ed: Editor;

    constructor(ed: Editor) {
      this.ed = ed; // Parent class.
    }

    public format(html: string) {
      if (/\[gallery\s/i.test(html)) {
        return html; // Use shortcode.

      } else if (!/<img\s.*?src=['"]([^'"]+)/i.test(html)) {
        return html; // Unable to locate `src=""`.
      }
      let r = ''; // Initialize response.

      $.each(html.split(/[\r\n]+/), (i, html) => {
        let src, href, alt, cls, align, width;

        if (!(src = /<img\s.*?src=['"]([^'"]+)/i.exec(html))) {
          return; // Unable to locate `src=""`.
        }
        // ---

        href = /<a\s.*?href=['"]([^'"]+)/i.exec(html);
        alt = /<img\s.*?alt=['"]([^'"]+)/i.exec(html);
        cls = /<img\s.*?class=['"]([^'"]+)/i.exec(html);
        align = /\[caption\s.*?align=['"]([^'"]+)/i.exec(html);
        width = /(?:<img|\[caption)\s.*?width=['"]([^'"]+)/i.exec(html);

        if (href) { // Make href & src shorter.
          href[ 1 ] = href[ 1 ].replace(/^https?:\/{2}/i, '//');
        }
        src[ 1 ] = src[ 1 ].replace(/^https?:\/{2}/i, '//');

        cls = !cls && align ? align : cls;
        if (cls && /alignnone/i.test(cls[ 1 ])) {
          cls = null; // Ignore `alignnone`.
        }

        // ---

        r += '\n'; // Initialize.

        if (this.ed.data.settings.mediaInserts === 'html') {
          // Pure HTML markup in this case.

          if (href) {
            r += '<a href="' + _.escape(href[ 1 ]) + '">';
          }
          r += '<img src="' + _.escape(src[ 1 ]) + '"';

          if (alt) {
            r += ' alt="' + _.escape(alt[ 1 ]) + '"';
          }
          if (cls && /alignleft/i.test(cls[ 1 ])) {
            r += ' class="alignleft"';
          } else if (cls && /aligncenter/i.test(cls[ 1 ])) {
            r += ' class="aligncenter"';
          } else if (cls && /alignright/i.test(cls[ 1 ])) {
            r += ' class="alignright"';
          }
          if (width) {
            r += ' width="' + _.escape(width[ 1 ]) + '"';
          }
          r += ' />';

          if (href) {
            r += '</a>';
          }

        } else { // Use Markdown (default).

          if (href) {
            r += '[';
          }
          r += '![';

          if (alt) {
            r += alt[ 1 ]
              .replace(/\[/g, '(')
              .replace(/\]/g, ')');
          }
          r += '](';
          r += src[ 1 ]
            .replace(/\(/g, '[')
            .replace(/\)/g, ']');
          r += ')';

          if (cls || width) {
            r += '{';
          }
          if (cls) {
            if (/alignleft/i.test(cls[ 1 ])) {
              r += '.alignleft';
            } else if (/aligncenter/i.test(cls[ 1 ])) {
              r += '.aligncenter';
            } else if (/alignright/i.test(cls[ 1 ])) {
              r += '.alignright';
            }
          }
          if (cls && width) {
            r += ' width=' + _.escape(width[ 1 ]);
          } else if (width) {
            r += 'width=' + _.escape(width[ 1 ]);
          }
          if (cls || width) {
            r += '}';
          }
          if (href) {
            r += '](';
            r += href[ 1 ]
              .replace(/\(/g, '[')
              .replace(/\)/g, ']');
            r += ')';
          }
        }
      });
      return r + '\n';
    }
  }
}
