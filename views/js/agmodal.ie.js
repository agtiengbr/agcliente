function AgModalIe(params)
{
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

    if (this.onclose !== undefined) {
        this.onclose = params.onclose;
    }     
    
    this.appendToDom();
}


AgModalIe.prototype.appendToDom = function()
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

AgModalIe.prototype.setDomId = function(dom_id)
{
    if (document.getElementById(dom_id) !== null) {
        throw new Error("Já existe um elemento com o id " + dom_id + ".");
    }

    this._dom_id = dom_id;
}

AgModalIe.prototype.getDomId = function()
{
    return this._dom_id
}

AgModalIe.prototype.setContent = function(content)
{
    this._content = content;
}

AgModalIe.prototype.getContent = function()
{
    return this._content
}

AgModalIe.prototype.btn_close_clicked = function()
{
    this.agmodal.hide();
}

AgModalIe.prototype.modal_clicked = function(e)
{
    if (e.target == this) {
        this.agmodal.hide();
        var event = new Event('agmodal_close');
        this.dispatchEvent(event);
    }
}   

AgModalIe.prototype.open = function()
{
     document.getElementById(this.dom_id).classList.add('on');
}

AgModalIe.prototype.hide = function()
{
    document.getElementById(this.dom_id).classList.remove('on');

    if (this.onclose !== undefined) {
        this.onclose();
    }
}