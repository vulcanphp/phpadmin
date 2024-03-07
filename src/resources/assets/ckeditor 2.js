window.addEventListener('load', function(){
    document.querySelectorAll('oembed[url]').forEach( element => {
        // get just the code for this youtube video from the url
        let vCode = element.attributes.url.value.split('?v=')[1];
        // paste some BS5 embed code in place of the Figure tag
        element.parentElement.outerHTML= `
            <div class="ratio ratio-16x9">
                <iframe src="https://www.youtube.com/embed/${vCode}?rel=0" style="width:100%; aspect-ratio: 16/9;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>`;
    });
})