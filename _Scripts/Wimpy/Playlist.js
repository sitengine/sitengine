/**
 * Playlist for Wimpy_Player
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 *
 * @copyright  Copyright (c) 2008, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    New BSD License
 */
 
var Wimpy_Playlist = new Class({
	
	_tracks: null,
	_key: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	
	initialize: function(){
		this._tracks = $H({});
	},
	
	addTrack: function(id, ref, title, artist){
		var data = $H({
			ref: ref,
			title: title || '',
			artist: artist || '',
			number: this._tracks.getLength() + 1
		});
		this._tracks.include(id, data);
	},
	
	get: function(){
		var s = '<playlist>';
		this._tracks.each(function(val, key){
			s += '<item>';
			s += '<title>' + this._tracks[key].title + '</title>';
			s += '<artist>' + this._tracks[key].artist + '</artist>';
			s += '<filename>' + this._getRef(this._tracks[key].ref) + '</filename>';
			s += '</item>';
		}.bind(this));
		return s + '</playlist>';
	},
	
	getTrackNumber: function(id){
		if(this._tracks.has(id)){
			return this._tracks.get(id).number;
		}
		return null;
	},
	
	getTrackArtist: function(id){
		if(this._tracks.has(id)){
			return this._tracks.get(id).artist;
		}
		return null;
	},
	
	getTrackTitle: function(id){
		if(this._tracks.has(id)){
			return this._tracks.get(id).title;
		}
		return null;
	},
	
	getTrackId: function(number){
		var id = null;
		this._tracks.each(function(val, key){
			if(val.number == number){ id = key; }
		});
		return (id === null) ? null : id;
	},
	
	isLastTrack: function(id){
		var count = 0;
		var isLast = false;
		this._tracks.each(function(val, key){
			if(key == id && count == this._tracks.getLength() - 1) {
				isLast = true;
			}
			count++;
		}, this);
		return isLast;
	},
	
	exists: function(id){
		return this._tracks.has(id);
	},
	
	getLength: function(){
		return this._tracks.getLength();
	},
	
	_getRef: function(s){
        var o = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
        s = s.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		
        while(i < s.length)
        {
            enc1 = this._key.indexOf(s.charAt(i++));
            enc2 = this._key.indexOf(s.charAt(i++));
            enc3 = this._key.indexOf(s.charAt(i++));
            enc4 = this._key.indexOf(s.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            o = o + String.fromCharCode(chr1);
            if(enc3 != 64) { o = o + String.fromCharCode(chr2); }
            if(enc4 != 64) { o = o + String.fromCharCode(chr3); }
        }
		return this._utf8Decode(o);
    },
    
    _utf8Decode: function (utf) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while(i < utf.length)
        {
            c = utf.charCodeAt(i);
            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utf.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utf.charCodeAt(i+1);
                c3 = utf.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
});