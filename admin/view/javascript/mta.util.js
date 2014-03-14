MTA_UTIL = {
	sprintf02f : function(_v) {
		try {
			_v = (parseInt(Math.round(_v * 100)) / 100) + '';			
			if(/^\d+$/.test(_v)) {
				_v += '.00';
			} else if(/^\d+\.\d$/.test(_v)) {
				_v += '0';
			}
			_v = _v.replace(/(\.\d\d).*$/, '$1');
		} catch(e) {
			_v = '';
		}
		return _v;
	},

	sprintf04f : function(_v) {
		try {
			_v = (parseInt(Math.round(_v * 10000)) / 10000) + '';			
			if(/^\d+$/.test(_v)) {
				_v += '.0000';
			} else if(/^\d+\.\d$/.test(_v)) {
				_v += '000';
			} else if(/^\d+\.\d{2}$/.test(_v)) {
				_v += '00';
			} else if(/^\d+\.\d{3}$/.test(_v)) {
				_v += '0';
			}
			_v = _v.replace(/(\.\d{4}).*$/, '$1');
		} catch(e) {
			_v = '';
		}
		return _v;
	},

	ucwords : function(str, space) {
		if(!space) space = ' ';
    return (str + '').replace(/^([a-z])|[_\-\s]+([a-z])/g, function ($1) {
       return $1.toUpperCase();
 	  }).replace(/[_\-\s]+/g, space);
	}
};

