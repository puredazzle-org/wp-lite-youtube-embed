(function () {
  if (typeof tinymce === 'undefined') return;

  tinymce.PluginManager.add('lite_youtube', function (editor) {
    editor.on('init', function () {
      var doc = editor.getDoc();

      var script = doc.createElement('script');
      script.src = window.liteYouTubeAssets.js;
      doc.head.appendChild(script);

      var link = doc.createElement('link');
      link.rel = 'stylesheet';
      link.href = window.liteYouTubeAssets.css;
      doc.head.appendChild(link);
    });
  });
})();
