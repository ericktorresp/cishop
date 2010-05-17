CRIMS.PipeSingle = new Class({

	Extends: CRIMS.Pipe,

	initialize: function(core, options){
		this.parent(core, options);
		this.type = 'uni';
		this.ape.fireEvent('uniPipeCreate',[this, options]);
	}
});
