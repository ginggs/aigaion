tinyMCEPopup.requireLangPack("latex");

var LaTeXDialog = {
  parent : null,
  
  create : false,

  init : function () {
    var f = document.forms[0];

    if (window.innerWidth && window.innerWidth != 400) window.innerWidth = 400;
    // Get the selected contents as text and place it in the input
    LaTeXDialog.parent = tinyMCEPopup.editor.selection.getNode ();
    while (LaTeXDialog.parent.namespaceURI && LaTeXDialog.parent.namespaceURI != "http://www.w3.org/1999/xhtml") {
      LaTeXDialog.parent = parent.parentNode;
    }
    if (LaTeXDialog.parent.nodeName.toLowerCase () == 'code' && LaTeXDialog.parent.getAttribute ('class').search (/\blatex\b/) > -1) {
      f.latex.value = LaTeXDialog.parent.getAttribute ("title");
    } else {
      LaTeXDialog.create = true;
    }
  },

  insert : function() {
    //tinyMCEPopup.editor.execCommand('mceInsertContent', false, document.forms[0].someval.value);
    if (LaTeXDialog.create) {
      var code = document.createElement ("code");
      //tinyMCEPopup.editor.execCommand ('mceInsertContent', false, '<code class="latex" title="'+document.forms[0].latex.value+'">'+document.forms[0].latex.value+'</code>');
      tinyMCEPopup.editor.execCommand ('mceInsertContent', false, '<code class="latex" title="'+document.forms[0].latex.value+'">'+tinyMCEPopup.editor.serializer.serialize (window.tex.parseMath (document.forms[0].latex.value))+'</code>');
    } else {
      LaTeXDialog.parent.setAttribute ("title", document.forms[0].latex.value);
      while (LaTeXDialog.parent.hasChildNodes ()) {
        LaTeXDialog.parent.removeChild (LaTeXDialog.parent.firstChild);
      }
      LaTeXDialog.parent.appendChild (document.createTextNode (document.forms[0].latex.value));
    }
    tinyMCEPopup.close();
  }
};

tinyMCEPopup.onInit.add (LaTeXDialog.init, LaTeXDialog);
