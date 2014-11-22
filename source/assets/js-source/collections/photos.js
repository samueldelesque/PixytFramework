app.collections.photos = Backbone.Collection.extend({
	url: "/photos",
	model: app.models.photo,
});