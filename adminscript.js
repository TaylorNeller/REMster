

function addSongForm() {
    songContainer = document.getElementById("song-container");
    htmlString = "<p>Name</p>"
    +"<input name='sname' type='text'/>"
    +"<p>Genre</p>"
    +"<input name='sname' type='text'/>	"	
    +"<p>Duration</p>"
    +"<input name='sname' type='text'/>"
    +"<p>Artists</p>"
    +"<input name='sname' type='text'/>";
    newElement = document.createElement("div");
    newElement.classList.add("add-song");
    newElement.innerHTML = htmlString;
    button = songContainer.children[songContainer.children.length - 1];
    songContainer.insertBefore(newElement, button);
}