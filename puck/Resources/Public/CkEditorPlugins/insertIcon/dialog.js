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
/*		{
			type:'text',
			id:'colorChooser',
			className:'colorChooser',
			label:'Color',
			setup:function(widget){
			var color = widget.data.color != '' ? widget.data.color:'';
			this.setValue(color);
			},
			commit:function(widget){
			widget.setData('color', this.getValue());
			}
		},
		{
			type:'button',label:'Color',style:'margin-top:1.35em',
			onClick:function(){
			editor.getColorFromDialog(function(color){
			document.getElementsByClassName('colorChooser')[0].getElementsByTagName('input')[0].value = color;
			}, this);
			}
		},
		{
			type:'text',id:'size',className:'size',label:'Size',setup: function(widget){this.setValue(widget.data.size);},
			commit: function(widget){widget.setData('size', this.getValue());}
		},*/
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
/*		{
		type:'select',id:'fixwidth',className:'iconSelect',label:'Fixed Width',items:[['No'],['Yes']],'default':'No',
			commit:function(widget){widget.setData('fixwidth',this.getValue());}
		},
		{
		type:'select',id:'bordered',className:'iconSelect',label:'Bordered',items:[['No'],['Yes']],'default':'No',
			commit:function(widget){widget.setData('bordered',this.getValue());}
		},
		{
		type:'select',id:'spinning',className:'iconSelect',label:'Spinning',items:[['No'],['Yes']],'default':'No',
			commit:function(widget){widget.setData('spinning',this.getValue());}
		},
		{
		type:'select',id:'rotating',className:'iconSelect',label:'Rotating',items:[['No'],['fa-rotate-90'],['fa-rotate-180'],['fa-rotate-270'],['fa-flip-horizontal'],['fa-flip-vertical'],['fa-flip-both']],'default':'No',
			commit:function(widget){widget.setData('rotating',this.getValue());}
		},*/

		]
		},
		{
		type:'hbox',
		widths:['33%','33%','33%'],
		children:[/*
		{
		type:'button',className:'iconSelect',label:'Brands '+Object.keys(faBrands).length,
			onClick:function(){
			document.getElementById('ck-insert-icons').innerHTML = faIcons(faBrands,'b');
			}
		},
		{
		type:'button',className:'iconSelect',label:'Regular '+Object.keys(faRegular).length,
			onClick:function(){
			document.getElementById('ck-insert-icons').innerHTML = faIcons(faRegular,'r');
			}
		},
		{
		type:'button',className:'iconSelect',label:'Solid '+Object.keys(faSolid).length,
			onClick:function(){
			document.getElementById('ck-insert-icons').innerHTML = faIcons(faSolid,'s');
			}
		},*/
/*		{
			type:'button',className:'iconSelect',label:'Puck '+Object.keys(puckCustom).length,
			onClick:function(){
				document.getElementById('ck-insert-icons').innerHTML = puckIcons(puckCustom);
			}
		}*/
		]
		},
		{type:'html',html:'<div id="ck-insert-icons">' + puckIcons(puckCustom) + '</div>'}
	]
	}],
	onOk:function () {
		clear();
		var dialog = this,icon = editor.document.createElement('span'),cls='';
/*		if(dialog.getValueOf('icon-library','fixwidth') == "Yes") cls += ' fa-fw';
		if(dialog.getValueOf('icon-library','bordered') == "Yes") cls += ' fa-border';
		if(dialog.getValueOf('icon-library','spinning') == "Yes") cls += ' fa-spin';
		if(dialog.getValueOf('icon-library','rotating') != "No") cls += ' '+dialog.getValueOf('icon-library','rotating');*/
		icon.setAttribute('class', 'char-icon '+dialog.getValueOf('icon-library','puck-icon')+cls);
/*		var style='';
		if(dialog.getValueOf('icon-library','colorChooser') !='')
		style += 'color:' + dialog.getValueOf('icon-library','colorChooser')+';';
		if(dialog.getValueOf('icon-library','size') !='')
		style += 'font-size:' + dialog.getValueOf('icon-library','size') + 'px';
		if(style) icon.setAttribute('style', style);*/


		icon.setAttribute('aria-hidden','true');
		icon.$.textContent = '­';
		editor.insertElement(icon);
	},
	onCancel:function () {
		clear();
	}
};
});