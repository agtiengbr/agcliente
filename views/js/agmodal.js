class AgModal {
 	constructor(params) {
 		if (typeof params === 'undefined') {
 			params = {};
 		}

 		if (typeof params.dom_id !== 'undefined') {
  			this.dom_id = params.dom_id;
  		}

  		if (typeof params.content !== 'undefined') {
  			this.content = params.content;
  		}

  		this.parent = document.getElementsByTagName('main')[0];
  		if (this.parent == null) {
  			this.parent = document.getElementById('content');
  		}

      if (this.parent == null) {
        this.parent = document.querySelector('body');
      }

		if (this.onclose !== 'undefined') {
  			this.onclose = params.onclose;
  		}  		

  		this.appendToDom();
 	}

 	/***************************** getters e setters ************************/

 	/********************* dom_id *******************/
 	set dom_id(dom_id) {
 		//verifica se já há um elemento com esse ID
 		if (document.getElementById(dom_id) !== null) {
 			throw new Error("Já existe um elemento com o id " + dom_id + ".");
 		}

 		this._dom_id = dom_id;
 	}

 	get dom_id() {
 		if (this._dom_id == '' || typeof this._dom_id === 'undefined') {
 			this.dom_id = (new Date().getTime()).toString(16);
 		}

 		return this._dom_id;
 	}

 	/********************* content *******************/

 	set content(content) {
 		this._content = content;
 	}

 	get content()
 	{
 		return this._content;
 	}

 	appendToDom()
 	{
 		var modal = document.createElement('div');
 		modal.classList.add('agmodal');
 		modal.id = this.dom_id;
 		modal.addEventListener('click', this.modal_clicked);
 		modal.agmodal = this;

 		var modal_content = document.createElement('div');
 		modal_content.classList.add('agmodal-content');

 		var btn_close = document.createElement('span');
 		btn_close.classList.add('close');
 		btn_close.textContent = 'x';
 		btn_close.agmodal = this;
 		modal_content.appendChild(btn_close);

 		btn_close.addEventListener('click', this.btn_close_clicked);

 		modal_content.appendChild(this.content);
 		modal.appendChild(modal_content);

 		this.content.classList.remove('hidden');

 		this.parent.appendChild(modal);
 	}


 	destroy()
 	{
 		var dom = document.getElementById(this.dom_id);
 		dom.parentNode.removeChild(dom);
 	}

 	/********************************** eventos *************************************/
 	btn_close_clicked()
 	{
 		this.agmodal.hide();
 	}

	modal_clicked(e)
 	{
 		if (e.target == this) {
 			this.agmodal.hide();
 			var event = new Event('agmodal_close');
 			this.dispatchEvent(event);
 		}
 	} 	

 	/********************************** demais métodos ******************************/
 	open()
 	{
 		document.getElementById(this.dom_id).classList.add('on');
 	}

 	hide()
 	{
 		document.getElementById(this.dom_id).classList.remove('on');

 		if (this.onclose !== null) {
 			this.onclose();
 		}
 	}
}