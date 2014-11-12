$(document).ready(function() {
    $('#gallery_setting_apply').click(applySettings);
});


function toggleBlackWhite(checked) {
    if (checked) {
        Metro_Gallery._containers.addClass('bw');
    } else {
        Metro_Gallery._containers.removeClass('bw');
    }
}

function toggleLightbox(checked) {
    if (checked) {
        Metro_Gallery._containers.addClass('lightbox');
    } else {
        Metro_Gallery._containers.removeClass('lightbox');
    }
}

function changeDirection(direction) {
    Metro_Gallery._tiles.each(function(index) {
        var tile      = $(this);
        var container = tile.parent();
        var scroller  = tile.find('.scroller');
        var images    = scroller.children('img');

        container.removeClass('vertical horizontal').addClass(direction);

        scroller.removeAttr('style');
        images.removeAttr('style');

        if (direction === 'vertical') {
            scroller.height(images.length * 100 + '%');
            images.height(100 / images.length + '%');
        } else {
            scroller.width(images.length * 100 + '%');
            images.width(100 / images.length + '%');
        }
    });
}

function changeColour(colour) {
    colour = colour.toLowerCase();
    var colour_array = ['blue', 'orange', 'red', 'yellow', 'green', 'grey', 'purple', 'darkblue', 'darkred', 'darkgreen', 'white'];

    Metro_Gallery._tiles.removeClass(colour_array.join(' '));

    if (colour) {
        Metro_Gallery._tiles.addClass(colour);
    } else {
        Metro_Gallery._tiles.each(function() {
            var random_colour = colour_array[Math.floor(Math.random() * (colour_array.length - 1))];
            $(this).addClass(random_colour);
        });
    }
}

function changeWidth(column) {
    if (column) {
        Metro_Gallery._containers.width(column * Metro_Gallery._configs.base_size + column * Metro_Gallery._configs.gutter);
    } else {
        Metro_Gallery._containers.width('auto');
    }

    setTimeout(function() {
        $(window).resize();
    }, 500);
}

function changeAnimation(animation) {
    if (animation) {
        var animations = ['flip', 'fade', 'scale'];

        Metro_Gallery._containers.removeClass(animations.join(' ')).addClass(animation);

        Metro_Gallery._tiles.removeClass('loaded');
        Metro_Gallery._tiles.each(function(index) {
            var tile = $(this);

            setTimeout(function() {
                tile.addClass('loaded');
            }, index * 100);
        });
    } else {
        Metro_Gallery._containers.hide()

        setTimeout(function() {
            Metro_Gallery._containers.show();
        }, 100);
    }
}

function applySettings() {
    var base_size = validateInt($('#base_size'), 100, 40, false);
    var gutter    = validateInt($('#gutter'), 10, 0, true);
    var scale     = validateFloat($('#scale'), 1.4);


    Metro_Gallery.setOptions({
        base_size: base_size,
        gutter:    gutter,
        scale:     scale
    });

    Metro_Gallery._containers.masonry('destroy');

    $('style').remove();
    Metro_Gallery._buildStyles();

    $('#main').addClass('loading');

    setTimeout(function() {
        $('#column_num').change();

        var column_width = Metro_Gallery._configs.base_size + Metro_Gallery._configs.gutter;

        Metro_Gallery._containers.masonry({
            itemSelector : '.tile',
            isAnimated   : !Metro_Gallery._use_css_animation,
            columnWidth  : column_width
        });

        setTimeout(function() {
            $('#main').removeClass('loading');
        }, 1000);
    }, 2500);
}


var validateInt = function(element, default_value, min, allow_zero) {
    var value = parseInt($.trim(element.val()), 10);

    if (isNaN(value) || (!allow_zero && value <= 0)) {
        value = default_value;
        element.val(value);
    } else if (value < min) {
        value = min;
        element.val(value);
    }

    return value;
};

var validateFloat = function(element, default_value) {
    var value = parseFloat($.trim(element.val()));

    if (isNaN(value) || value < 1) {
        value = default_value;
        element.val(value);
    }

    return value;
};