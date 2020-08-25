var timer; //this is a global variable

$(document).ready(function() {
    $('.result').on('click', function() {
        var url = $(this).attr('href');
        var id = $(this).attr('data-linkId');
        if (!id) {
            alert('data-linkId attribute not found');
        }
        increaseLinkClicks(id, url);
        return false;
    })

    $('.imageResults').on('layoutComplete', function() {
        $('.gridItem img').css('visibility', 'visible');
    });

    $('.imageResults').masonry({
        // options
        itemSelector: '.gridItem',
        columnWidth: 200,
        gutter: 5,
        isInitLayout: false
      });

    $('[data-fancybox]').fancybox({
        caption : function( instance, item ) {
            var caption = $(this).data('caption') || '';
            var siteUrl = $(this).data('siteurl') || '';
            if ( item.type === 'image' ) {
                caption = (caption.length ? caption + '<br />' : '') + '<a href="' + item.src + '">View image</a><br />' + '<a href="' + siteUrl + '">Visit page</a>';
            }
            return caption;
        },
        afterShow : function( instance, item) {
            increaseImageClicks(item.src);
        }
    });
})

function increaseLinkClicks(linkId, url)
{
    $.post('/Home/updateLinkClicks', {linkId : linkId})
    //this result is only passed in from the Ajax call
    .done(function($result) {
        if ($result != '') {
            alert($result);
            return;
        } 
        window.location.href = url;
    });
}

function increaseImageClicks(imageUrl)
{
    $.post('/Home/updateImageClicks', {imageUrl : imageUrl})
    //this result is only passed in from the Ajax call
    .done(function($result) {
        if ($result != '') {
            alert($result);
            return;
        } 
    });
}

function loadImage(src, className)
{
    var image = $('<img>');
    image.on('load', function() {
        $('.' + className + ' a').append(image);
        clearTimeout(timer);
        timer = setTimeout(function() {
            $('.imageResults').masonry();
        }, 500);
    });
    image.on('error', function() {
        $('.' + className).remove();
        $.post('/Home/removeBrokenImages', {src : src});
    })
    image.attr('src', src);
}