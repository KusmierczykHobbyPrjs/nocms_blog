
function onResize() {
    document.body.style.backgroundPosition = (document.body.clientWidth<920 ? 'left top': 'center top');
};

function onLoad() {
    onResize();
};
