define(['jquery', 'mod_library/jquery.ui.pos', 'mod_library/iconpicker'], function($, pos, iconpicker) {
    return {
        init: function() {
            $('#icon').iconpicker();
        }
    };
});