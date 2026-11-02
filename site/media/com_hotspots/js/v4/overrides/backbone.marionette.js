Handlebars.registerHelper('ifCond', function (v1, operator, v2, options) {

	switch (operator) {
		case '==':
			return (v1 == v2) ? options.fn(this) : options.inverse(this);
		case '===':
			return (v1 === v2) ? options.fn(this) : options.inverse(this);
		case '<':
			return (v1 < v2) ? options.fn(this) : options.inverse(this);
		case '<=':
			return (v1 <= v2) ? options.fn(this) : options.inverse(this);
		case '>':
			return (v1 > v2) ? options.fn(this) : options.inverse(this);
		case '>=':
			return (v1 >= v2) ? options.fn(this) : options.inverse(this);
		case '&&':
			return (v1 && v2) ? options.fn(this) : options.inverse(this);
		case '||':
			return (v1 || v2) ? options.fn(this) : options.inverse(this);
		case '!==':
			return (v1 !== v2) ? options.fn(this) : options.inverse(this);
		default:
			return options.inverse(this);
	}
});

Handlebars.registerHelper("last", function(array) {
	return array[array.length-1];
});

/**
 * Created by DanielDimitrov on 08.10.2014.
 */
Backbone.Marionette.TemplateCache.prototype.compileTemplate = function(rawTemplate) {
	//var settings = {
	//	evaluate: /\{\{(.+?)\}\}/g,
	//	interpolate: /\{\{=(.+?)\}\}/g,
	//	escape: /\{\{-(.+?)\}\}/g
	//};

	return Handlebars.compile(rawTemplate);
};