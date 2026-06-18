
function myFunction(id) {
    var dummy = document.createElement("textarea");
    document.body.appendChild(dummy);
    dummy.value = id.getAttribute('data-custom-attribute');
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
}