

CKEDITOR.dtd.$removeEmpty.i = 0;
CKEDITOR.dtd.$removeEmpty.span = 0;

CKEDITOR.plugins.add('insertIcon',{
    icons:'insertIcon',
    init:function(editor){
        editor.addCommand('insertIcon', new CKEDITOR.dialogCommand('insertIconDialog',{allowedContent:'span(!icon--*) i(!icon--)'}));
        editor.ui.addButton('insertIcon',{label:'Theme icons',command:'insertIcon',toolbar:'insert',icon:this.path + 'icons/insertIcon.svg'});
        CKEDITOR.dialog.add('insertIconDialog', this.path + 'dialog.js');
        CKEDITOR.document.appendStyleSheet(this.path + 'insertIcon.css');
    }
});

