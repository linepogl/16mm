
html = function(s) { return s===null || s===undefined ? '' : document.createTextNode(s).nodeValue; };
empty = function(s){ return s === '' || s === null || s === undefined || (typeof s === 'Array' && s.length === 0); };
join = function(a){ return (a === null || a === undefined) ? '' : a.join(', '); };

mmx = {

     ShowTab:function(tab){
        jQuery('.mmx-tab').removeClass('active');
        jQuery('#mmx-tab-'+tab).addClass('active');
        //jQuery('.mmx-tab-page').hide();
        //jQuery('#mmx-tab-page-'+tab).show();
    }
    ,Search:function() {
        var s = jQuery('#searchstring').val();
        console.log(s);
        this.HideSearch();
    }
    ,ShowSearch:function() {
        jQuery('#mmx-search').fadeIn(200);
        jQuery('#searchstring').focus();
    }
    ,HideSearch:function() {
        jQuery('#mmx-search').fadeOut(500);
    }
    ,ToggleSearch:function() {
        if (jQuery('#mmx-search').is(':visible')) this.HideSearch(); else this.ShowSearch();
    }


    ,movie_tile_template : '<div class="mmx-tile mmx-movie-tile">'
        +'<div class="image"></div>'
        +'<div class="details" >'
        +'<div class="col-0" ></div>'
        +'<div class="col-1" >'
        +'<div class="original-title"><span class="label">Original title</span><span class="value"></span></div>'
        +'<div class="countries"><span class="label">Country</span><span class="value"></span></div>'
        +'<div class="genres"><span class="label">Genre</span><span class="value"></span></div>'
        +'</div>'
        +'<div class="col-2" >'
        +'<div class="languages"><span class="label">Language</span><span class="value"></span></div>'
        +'<div class="runtime"><span class="label">Runtime</span><span class="value"></span></div>'
        +'<div class="episodes"><span class="label">Episodes</span><span class="value"></span></div>'
        +'</div>'
        +'</div>'
        +'<div class="main" >'
        +'<div class="title"></div>'
        +'</div>'
        +'</div>'

    ,movie_side_template : '<div class="mmx-side mmx-movie-side">'
        +'<div class="image"></div>'
        +'<div class="title"></div>'
        +'<div class="details" >'
        +'<div class="original-title"><span class="label">Original title</span><span class="value"></span></div>'
        +'<div class="genres"><span class="label">Genre</span><span class="value"></span></div>'
        +'<div class="runtime"><span class="label">Runtime</span><span class="value"></span></div>'
        +'<div class="countries"><span class="label">Country</span><span class="value"></span></div>'
        +'<div class="languages"><span class="label">Language</span><span class="value"></span></div>'
        +'<div class="episodes"><span class="label">Episodes</span><span class="value"></span></div>'
        +'</div>'
        +'<div class="description"></div>'

    ,FillMovieTemplate:function(movie,template){
        var xid = movie.Type+movie.id;
        var s;
        var r = jQuery(template);
        r.data('id',xid);
        r.find('.title').html(html(movie.Title));
        s = html(join(movie.Languages)); r.find('.languages').toggle(s!=='').find('.value').html(s);
        s = html(join(movie.Countries)); r.find('.countries').toggle(s!=='').find('.value').html(s);
        s = html(join(movie.Genres));    r.find('.genres').toggle(s!=='').find('.value').html(s);
        s = html(movie.Runtime);              r.find('.runtime').toggle(s!=='').find('.value').html(s);
        s = html(movie.OriginalTitle);        r.find('.original-title').toggle(s!=='').find('.value').html(s);
        s = html(movie.Description);        r.find('.description').toggle(s!=='').html(s);
        r.find('.episodes').toggle(!empty(movie.Episodes)).find('.value').html(html(movie.Episodes+(empty(movie.Seasons)?'':' in '+movie.Seasons+' '+(movie.Seasons===1?'season':'seasons'))));
        if (!empty(movie.Image)) r.find('.image').css({'background-image':'url('+movie.Image+')'});
        r.click(function(){window.mmx.SetSideMovie(movie);});
        return r;
    }
    ,AddMovieTile:function(movie){
        jQuery('#mmx-main').append(this.FillMovieTemplate(movie,this.movie_tile_template));
    }
    ,SetSideMovie:function(movie){
        jQuery('#mmx-side-bar-actors').hide();
        jQuery('#mmx-side-bar-movies').show().html( this.FillMovieTemplate(movie,this.movie_side_template));

        jQuery('#mmx-main').css({
            'margin-right': (jQuery('#mmx-bar-movies:visible').width() + jQuery('#mmx-side-bar-movies:visible').width()) + 'px'
            ,
            'margin-left': (jQuery('#mmx-bar-actors:visible').width() + jQuery('#mmx-side-bar-actors:visible').width()) + 'px'
        });

    }


};



Mousetrap.bind(['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'],function(){window.mmx.ShowSearch();});
Mousetrap.bindGlobal(['esc'],function(){window.mmx.ToggleSearch();});
