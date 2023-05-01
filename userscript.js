


function toggleLike(sid) {

    event.preventDefault(); // prevent the form from being submitted

  // make an AJAX request to your PHP script
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'like.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.send('like-id=' + sid);


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
var playingHowl = null;

// 0: sid
// 1: aid (for cover art)
// 2: name
// 3: duration
// 4: other songs in collection
// 5+: artists
var currSong = null;

var otherMap = null;

// called when playing a song from a new collection. Updates all global info.
function playNew(songData) {
    if (playingHowl != null) {
        playingHowl.unload();
    }
    otherMap = new Map();
    currSong = songData.split(", ");
    var otherSongs = currSong[4].split("/");
    for (var i = 0; i < otherSongs.length; i++) {
        var currOther = otherSongs[i].split(".");
        otherMap.set(currOther[0], currOther);
    }
    var srcLink = 'songs/' + songData[0] + '.mp3';
    playingHowl = new Howl({
        src: [srcLink]
      });
    
    playingHowl.play();
    document.getElementById("playButton").src= "art/assets/pause.png";
    showNowPlayingInfo(currSong);
}

function showNowPlayingInfo(songData) {
    const currSid = songData[0];
    const currAid = songData[1];
    const currName = songData[2].replaceAll('"', "");
    var currArtists = "";
    for(var i = 5; i < songData.length; i++) {
        currArtists += songData[i] + ", ";
    }
    currArtists = currArtists.replaceAll('"', "");
    var returnString = "";
    returnString += "<DIV class='row'>";
    returnString += "<DIV class='col-md-2 playingInfoCover'>\n";
    returnString += "<img src='art/cover/" + currAid + ".png' alt='current album cover' " + 
				"class='playingInfoCover'>";
    returnString += "</DIV>\n";
    returnString += "<DIV class='col-md-10'>\n";
    returnString += "<DIV class='row textRow'>" + currName + "</DIV>";
    returnString += "<DIV class='row textRow'>" + currArtists + "</DIV>";
    returnString += "</DIV>\n";
    returnString += "</DIV>\n";
    var newBox = document.createElement("div");
    newBox.innerHTML = returnString;
    document.getElementById("nowPlaying").replaceWith(newBox);
}

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

function getNext() {
    var currSid = currSong[0];
    var otherSids = Array.from(otherMap.keys());
    var nextSid = otherSids[(otherSids.indexOf(currSid) + 1)];
    var nextSong = otherMap.get(nextSid);
    playNext(nextSong);
}

function playNext() {
    if (playingHowl != null) {
        var nextSong = getNext()
    }
}