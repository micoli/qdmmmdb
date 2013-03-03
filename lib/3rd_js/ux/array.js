Ext.ux.array = new(function() {
	var that = this;

	that.insertAt = function(array, index) {
		var arrayToInsert = Array.prototype.splice.apply(arguments, [2]);
		Array.prototype.splice.apply(array, [index, 0].concat(arrayToInsert));
		return array;
	}

	that.insertArrayAt = function(array, index, arrayToInsert) {
		Array.prototype.splice.apply(array, [index, 0].concat(arrayToInsert));
		return array;
	}
})();