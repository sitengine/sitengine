/**
 * Wimpy wrapper class
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 *
 * @copyright  Copyright (c) 2008, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    New BSD License
 */


var Wimpy_Player = new Class({
	
	_isReady: false,
	_timer: null,
	_playlist: null,
	_currentId: null,
	_win: null,
	
	
	initialize: function(playlist){
		this._playlist = playlist;
	},
	
	
	// handleWimpyInit() callback must be directed here
	ready: function(){
		this._isReady = true;
		this._onReady();
	},
	
	
	// handlTrackStarted() callback must be directed here
	trackStarted: function(){
		var self = this;
		$clear(this._timer);
		this._setCurrentId(this._playlist.getTrackId(wimpy_getTrackInfo().tracknumber));
		this._onTrackStarted();
		this._timer = setInterval(function(){
			self._onPlaying();
		}, 100);
	},
	
	
	// handleTrackDone() callback must be directed here
	trackDone: function(){
		$clear(this._timer);
		this._onTrackDone();
		this._setCurrentId(null);
	},
	
	
	_trackStop: function(){
		$clear(this._timer);
		this._onTrackStop();
		this._setCurrentId(null);
	},
	
	
	_setCurrentId: function(id){
		this._currentId = id;
	},
	
	
	getCurrentId: function(){
		return this._currentId;
	},
	
	
	isCurrentId: function(id){
		return (this._currentId == id);
	},
	
	
	stop: function(){
		wimpy_stop();
		this._trackStop(); // no callback function exists for this event - call manually
	},
	
	
	_done: function(){
		wimpy_stop();
		this.trackDone(); // no callback function exists for this event - call manually
	},
	
	
	toggle: function(trackId)
	{
		if(!this._isReady)
		{
			this._onNotReady(trackId);
		}
		else {
			if(this.isCurrentId(trackId)) { this.stop(); }
			else {
				if(this.getCurrentId() !== null) { this.stop(); }
				var nr = this._playlist.getTrackNumber(trackId);
				if(nr !== null) { wimpy_gotoTrack(nr); }
			}
		}
	},
	
	
	_previewAndFadeOut: function(previewLength)
	{
		if(wimpy_getPlayerState().current >= previewLength)
		{
			$clear(this._timer);
			var countdown = 100;
			var self = this;
			
			this._timer = setInterval(function(){
				wimpy_setVolume(countdown);
				countdown -= 1;
				if(countdown <= 0) {
					if(self._playlist.isLastTrack(self.getCurrentId())) {
						self._done();
					}
					else {
						self._done();
						wimpy_next();
					}
					wimpy_setVolume(100);
				}
				self._dbg(countdown);
			}, 10);
		}
	},
	
	
	detach: function(url, width, height, startPlayingOnload)
	{
		url += '?detached=1';
		
		if(this.getCurrentId() !== null)
		{
			// currently playing - add trackId so detached player starts playing this track
			url += '&trackId=' + this.getCurrentId();
			this.stop();
		}
		
		//var winName = "a" + randomNumber(1, 1000);
		var winName = 'dPlayer';
		this._win = window.open(url, winName, 'width=' + width + ',height=' + height + ',scrollbars=yes');
	},
	
	
	unDetach: function(){
		if(this._win) { this._win.close(); }
	},
	
	
	getQueryParams: function(url){
		var args = url.replace(/.*\?(.*)$/g, "$1").split('&');
		var params = $H({});
		
		args.each(function(pair, key){
			var parts = pair.split('=');
			params.include(parts[0], parts[1]);
		});
		return params;
	},
	
	
	makeNiceTimeFromSeconds: function(seconds, separator){
		var separator = (separator == undefined) ? ':' : separator;
		var seconds = parseInt(seconds, 10);
		var h = (seconds >= 3600) ? Math.floor(seconds / 3600) : 0;
		var m = ((seconds - h * 3600) >= 60) ? Math.floor((seconds - h * 3600) / 60) : 0;
		var s = seconds - (h * 3600) - (m * 60);
		var hh = (h < 10) ? '0' + h : h;
		var mm = (m < 10) ? '0' + m : m;
		var ss = (s < 10) ? '0' + s : s;
		return ((h > 0) ? hh + separator : '') + mm + separator + ss;
	},
	
	
	printPlaylist: function(){
		var s = '';
		var playlist = wimpy_getPlaylist();
		for(var x = 0; x < playlist.length; x++) { s += unescape(playlist[x].filename) + "\n"; }
		this._dbg(s);
	},
	
	
	printPlayerState: function(){
		var s = '';
		var state = wimpy_getPlayerState();
		for(var prop in state) { s += prop + " = " + state[prop] + "\n"; }
		this._dbg(s);
	},
	
	
	printLoadState: function(){
		var s = '';
		var state = wimpy_getLoadState();
		for(var prop in state) { s += prop + " = " + state[prop] + "\n"; }
		this._dbg(s);
	},
	
	
	printTrackInfo: function(){
		var s = '';
		var state = wimpy_getTrackInfo();
		for(var prop in state) { s += prop + " = " + state[prop] + "\n"; }
		this._dbg(s);
	},
	
	
	_getDefaultConfig: function(swf, reg){
		return {
			wimpySwf: swf,
			wimpyReg: reg,
			wimpyWidth: 1,
			wimpyHeight: 1,
			startPlayingOnload: "no",
			autoAdvance: "yes",
			sortField: "none",
			playlist: this._playlist.get()
		};
	},
	
	
	make: function(element, swf, reg){
		var cfg = this._getDefaultConfig(swf, reg);
		makeWimpyPlayer(cfg, element);
	},
	
	
	_onNotReady: function(trackId){
		// called when user hits play before player is loaded
	},
	
	_onReady: function(){
		// called when the player has finished loading
	},
	
	_onTrackStarted: function(){
		// called when user hits play
	},
	
	_onPlaying: function(){
		// called every 50 milliseconds while a track is playing
	},
	
	_onTrackDone: function(){
		// called when playhead reaches end of track
	},
	
	_onTrackStop: function(){
		// called when user hits stop or track has finished
	},
	
	_dbg: function(s){
		// print _dbg output
	}
});