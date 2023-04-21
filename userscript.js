


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

// function playSong(sid) {
//     var srcLink = 'songs/' + sid + '.mp3';
//     var sound = new Howl({
//         src: [srcLink]
//       });
      
//     sound.play();
// }