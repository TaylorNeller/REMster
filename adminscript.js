var songCounter = 0;

function counterReset() {
    songCounter = 0;
}

function addSongForm() {
    songContainer = document.getElementById("song-container");
    htmlString = htmlString = `<div class="add-song">
    <p class="song-p">Name</p>
    <input name="sname[${songCounter}]" class="fm-input" type="text" required/>
    
    <p class="song-p">Genre</p>
    <input name="sgenre[${songCounter}]" class="fm-input" type="text" required/>		
    
    <p class="song-p">Duration</p>
    <input name="sdur[${songCounter}]" class="fm-input" type="text" required/>
    
    <p class="song-p">Artists</p>
    <input name="sarts[${songCounter}]" class="fm-input" type="text" required/></div>
    
    <p class="song-p">Upload MP3:</p>
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
    <input name="smp3[${songCounter}]" type="file" required>
    `;
    newElement = document.createElement("div");
    newElement.classList.add("add-song");
    newElement.innerHTML = htmlString;
    br = songContainer.children[songContainer.children.length - 1]; // 2 buttons and <br> at end
    songContainer.insertBefore(newElement, br);

    songCounter++;
}

function removeSongForm() {
    console.log("hm");

    if (songCounter > 0) {
    
        songContainer = document.getElementById("song-container");
        lastSong = songContainer.children[songCounter];
        songContainer.removeChild(lastSong);
    
        songCounter--;
    }

}