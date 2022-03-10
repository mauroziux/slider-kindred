document.addEventListener( 'DOMContentLoaded', function() {
    var splide = new Splide( '.splide' ,{
        type   : 'loop',
        perPage: 1,
        speed:300,
        autoplay:true
    });
    splide.mount();
} );