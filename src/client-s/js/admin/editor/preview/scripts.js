(function ($) {
  $(window).on('load', function () {
    var $div = $('#md-preview-div');

    var locHostname = location.hostname.toLowerCase(),
      locPathname = location.pathname.replace(/^\//, '');

    var esqJqA = function (str) {
      return str.replace(/(:|\.|\[|\]|,|=|@)/g, '\\$1');
    };

    $div.on('click', 'a[href*="#"]:not([href="#"])', function (e) {
      var $target, // Initialize only.
        hostname = this.hostname.toLowerCase(),
        pathname = this.pathname.replace(/^\//, '');

      if (hostname !== locHostname) {
        return; // Not applicable.
      } else if (pathname !== locPathname) {
        return; // Not applicable.
      }
      if (!($target = $(this.hash)).length) {
        $target = $('[name="' + esqJqA(this.hash.slice(1)) + '"]');
      }
      if (!$target.length) {
        return; // Nothing to do.
      }
      e.preventDefault(), e.stopImmediatePropagation();
      $('html, body').animate({ scrollTop: $target.offset().top }, 500);
    });
  });
})(jQuery);
