var Utils = {
	// stripslashes equivalent of php (via phpjs.org)
	stripslashes: function(str) {
		return (str + '').replace(/\\(.?)/g, function (s, n1) {
			switch (n1) {
				case '\\':
					return '\\';
				case '0':
					return '\u0000';
				case '':
					return '';
				default:
					return n1;
			}
		});
	},
	unixTimestamp: function(date) {
		return Math.round((date == null ? new Date() : date).getTime() / 1000);
	}
};

module.exports = Utils;