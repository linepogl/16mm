

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



};



Mousetrap.bind(['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'],function(){window.mmx.ShowSearch();});
Mousetrap.bindGlobal(['esc'],function(){window.mmx.ToggleSearch();});
