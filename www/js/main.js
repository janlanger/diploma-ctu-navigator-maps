$("a[data-confirm]").live("click", function(event) {

    if (!confirm(this.getAttribute("data-confirm"))) event.preventDefault();
});