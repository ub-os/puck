function klick(el) {
	var className = el.childNodes[0].getAttribute('class');
	var dialog = CKEDITOR.dialog.getCurrent();
	dialog.getContentElement('icon-library','puck-icon').setValue(className);
	el.className = el.className.replace('active','');
	el.className += ' active';
}
function searchIcon(val){
	var fas = document.getElementById('ck-insert-icons');
	var a = fas.getElementsByTagName('a');
	for(var i = 0, len = a.length, el, atr; i < len; i ++){
		el = a[i];
		atr = el.childNodes[0].getAttribute('class');
		if(atr && atr.indexOf(val) >= 0){
			el.style.display = 'inline-block';
		}else{
			el.style.display = 'none';
		}
	}
}
function clear(){
	var icons = document.getElementById('ck-insert-icons');
	var activeIcon = icons.getElementsByClassName('active');
	var icon = icons.getElementsByTagName('a');
	for (var i=0; i < activeIcon.length; ++i) {
		activeIcon[i].className = activeIcon[i].className.replace('active','');
	}
	for(var j=0; j < icon.length; ++j){
		icon[j].className = '';
		icon[j].style.display = '';
		icon[j].getElementsByTagName('span')[0].style.color = '';
	}
}
CKEDITOR.dialog.add('insertIconDialog', function (editor) {
	var puckCustom = {"angle-down-regular":61697,"angle-left-regular":61698,"angle-right-regular":61699,"angle-up-regular":61700,"arrow-down-regular":61701,"arrow-left-regular":61702,"arrow-right-regular":61703,"arrow-up-regular":61704,"circle-play-duotone":61705,"close":61706,"envelope-solid":61707,"facebook":61708,"instagram":61709,"linkedin":61710,"plus-regular":61711,"spinner-circle":61712,"tiktok":61713,"website-logo":61714,"xing":61715,"xmark-regular":61716,"youtube":61717};

function puckIcons(custom) {
	var icons='';
	for(var x in custom){
		icons += '<a href="#" onclick="klick(this);return false;"><span class="icon--'+x+'"></span></a>';
	}
	return icons;
}
return {
	title:'Puck icons',
	minWidth:500,
	minHeight:400,
	resizable:false,
	contents:[{
	id:'icon-library',
	label:'Add icon',
	elements:[
		{
		type:'hbox',
		widths:['25%','10%','15%','50%'],
		children:[
		{
			type:'text',id:'faSearch',className:'faSearch',label:'Search',onKeyUp:function(e){searchIcon(e.sender.$.value);}
		},
		{
			type:'text',id:'puck-icon',className:'iconSelect',label:'Selected',validate:CKEDITOR.dialog.validate.notEmpty("Select icon"),onLoad: function(){this.getInputElement().setAttribute('readOnly',true);},setup:function(widget){this.setValue(widget.data.class != '' ? widget.data.class:'');},commit:function(widget){widget.setData('class', this.getValue());}
		}
		]
		},
		{
		type:'hbox',
		widths:['15%','15%','15%','15%','40%'],
		children:[
		]
		},
		{
		type:'hbox',
		widths:['33%','33%','33%'],
		children:[
		]
		},
		{type:'html',html:'<div id="ck-insert-icons">' + puckIcons(puckCustom) + '</div>'}
	]
	}],
	onOk:function () {
		clear();
		var dialog = this,icon = editor.document.createElement('span'),cls='';
		icon.setAttribute('class', 'char-icon '+dialog.getValueOf('icon-library','puck-icon')+cls);
		icon.setAttribute('aria-hidden','true');
		icon.$.textContent = '­';
		editor.insertElement(icon);
	},
	onCancel:function () {
		clear();
	}
};
});