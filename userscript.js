


function toggleLike(sid, pid) {

    event.preventDefault(); // prevent the form from being submitted

    // make an AJAX request to your PHP script
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'like.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('like-sid=' + sid+'&like-pid=' + pid);
    // xhr.send('like-pid=');


    button = document.getElementById("like-"+sid);
    if (button.classList.contains('liked')) {
        button.classList.remove('liked');
        button.classList.add('not-liked');

    }
    else {
        button.classList.add('liked');
        button.classList.remove('not-liked');

    }
}

// following code handles all song playback functionality.
// howl object for current song.
var playingHowl = null;

// 0: sid
// 1: aid (for cover art)
// 2: name
// 3: duration
// 4+: artists
var currentSongData = null;

// maps SIDs to the rest of the information about that song, for each in a collection.
var otherSongsMap = null;

// globals controlling shuffling / repeating.
var shuffling = false;
var repeating = false;

// called when playing a song from a new collection. Updates all global info.
function loadNewData(jsData) {
    initializeVolumeSlider();
    otherSongsMap = new Map();
    currentSongData = jsData.split(", ");
    var otherSongs = currentSongData[4].split("/");
    otherSongs.splice(otherSongs.length - 1, 1);
    currentSongData.splice(4, 1);
    for (var i = 0; i < otherSongs.length; i++) {
        var currOther = otherSongs[i].split(".");
        otherSongsMap.set(currOther[0], currOther);
    }
    playSong();
}

// called after new data loaded or new song chosen. Plays the given song, and updates relevent info.
function playSong() {
    sid = currentSongData[0];
    if (playingHowl != null) {
        playingHowl.unload();
    }
    var srcLink = 'songs/' + sid + '.mp3';
    playingHowl = new Howl({
        src: [srcLink],
        onplay: function() {
            // start the progress bar animation
            progressInterval = setInterval(updateProgress, 50);
          },
          onpause: function() {
            // stop the progress bar animation
            clearInterval(progressInterval);
          },
          onstop: function() {
            // reset the progress bar
            updateProgress(0);
          }
      });
    playingHowl.play();
    playingHowl.on("end", function() {chooseNextSong("N");});
    document.getElementById("playButton").src = "art/assets/pause.png";
    document.getElementById("passSid").value = sid;
    document.getElementById("submitAdd").disabled = false;
    showNowPlayingInfo(currentSongData);
}

// chooses the next song based on the current options.
function chooseNextSong(nextChoice) {
    var currSid = currentSongData[0];

    var otherSids = Array.from(otherSongsMap.keys());
    var numSongs = otherSids.length;
    var index = otherSids.indexOf(currSid);
    if (repeating) {
        index = index;
    }
    else if (shuffling) {
        index = parseInt(Math.random() * (numSongs - 1));
    }
    
    else if (nextChoice == "N") {
        index++;
    }
    else if (nextChoice == "P") {
        if (playingHowl.seek() < 2) {
            index = Math.max(index - 1, 0);
        }
        else {
            index = index;
        }
    }
    var nextSid = otherSids[index % (numSongs)];
    var nextSong = otherSongsMap.get(nextSid);
    currentSongData = nextSong;
    playSong();
}

// displays the "now playing" info in the bottom-left.
function showNowPlayingInfo(songData) {
    const currSid = songData[0];
    const currAid = songData[1];
    const currName = songData[2].replaceAll('"', "");
    var currArtists = "";
    for(var i = 4; i < songData.length; i++) {
        currArtists += songData[i] + ", ";
    }
    currArtists = currArtists.replaceAll('"', "");
    currArtists = currArtists.substring(0, currArtists.length - 2);
    var returnString = "";
    returnString += "<DIV class='row align-items-center'>";
    returnString += "<DIV class='col-md-3 playingInfoCover'>\n";
    returnString += "<img src='art/cover/" + currAid + ".png' alt='current album cover' " + 
				"class='playingInfoCover'>";
    returnString += "</DIV>\n";
    returnString += "<DIV class='col-md-9'>\n";
    returnString += "<DIV class='row textRow'>" + currName + "</DIV>";
    returnString += "<DIV class='row textRow'>" + currArtists + "</DIV>";
    returnString += "</DIV>\n";
    returnString += "</DIV>\n";
    var newBox = document.createElement("div");
    newBox.id = "nowPlaying";
    newBox.innerHTML = returnString;
    document.getElementById("nowPlaying").replaceWith(newBox);
}

// handles pausing / playing and updates buttons.
function playOrPause() {
    if (playingHowl != null) {
        if (playingHowl.playing()) {
            playingHowl.pause();
            document.getElementById("playButton").src= "art/assets/play.png";
        }
        else if (!playingHowl.playing()) {
            playingHowl.play();
            document.getElementById("playButton").src= "art/assets/pause.png";
        }
    }
}

// toggles shuffle.
function toggleShuffle() {
    if (shuffling) {
        shuffling = false;
        document.getElementById("shflimg").src= "art/assets/shuffle.png";
    }
    else {
        shuffling = true;
        document.getElementById("shflimg").src= "art/assets/shuffleon.png";
    }
}

// toggles repeat.
function toggleRepeat() {
    if (repeating) {
        repeating = false;
        document.getElementById("rptimg").src= "art/assets/repeat.png";
    }
    else {
        repeating = true;
        document.getElementById("rptimg").src= "art/assets/repeaton.png";
    }    
}
  // update the progress bar
function updateProgress() {
    var progress = playingHowl.seek() / playingHowl.duration();
    document.getElementById('progress').style.width = progress * 100 + '%';
}

function initializeVolumeSlider() {
    var volumeSlider = document.getElementById('volume-slider');
    volumeSlider.addEventListener('input', setVolume);
}

function setVolume() {
    var volumeSlider = document.getElementById('volume-slider');
    playingHowl.volume(volumeSlider.value / 100);
}