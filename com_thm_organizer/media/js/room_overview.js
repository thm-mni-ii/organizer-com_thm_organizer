function showPostLoader()
{
    const body = document.body,
        background = document.createElement('div'),
        postloader = document.createElement('div');

    background.className = 'loading-background';
    body.appendChild(background);
    postloader.className = 'postloader';
    body.appendChild(postloader);
}