<?php
session_start();
include 'db.php';

if (!isset($_GET['code'])) {
    echo "No room code.";
    exit();
}

$roomCode = $_GET['code'];
$stmt = $conn->prepare("SELECT * FROM rooms WHERE room_code = ?");
$stmt->bind_param("s", $roomCode);
$stmt->execute();
$result = $stmt->get_result();

if ($room = $result->fetch_assoc()) {
    $videoFile = $room['video_filename'];
} else {
    echo "Room not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WatchTogether - Room <?php echo htmlspecialchars($roomCode); ?></title>
  <style>
    video { width: 720px; margin: 20px 0; }
    #videos { display: flex; gap: 20px; }
    video.remote { width: 300px; height: 200px; }
  </style>
</head>
<body>
  <h2>Room Code: <?php echo htmlspecialchars($roomCode); ?></h2>

  <video id="syncVideo" controls>
    <source src="<?php echo htmlspecialchars($videoFile); ?>" type="video/mp4">
    Your browser does not support the video tag.
  </video>
  <br>
  <button id="startWatch">Start Watch</button>

  <div id="videos">
    <video id="localVideo" autoplay muted></video>
    <video id="remoteVideo" class="remote" autoplay></video>
  </div>

  <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
  <script>
    const roomCode = "<?php echo $roomCode; ?>";
    const video = document.getElementById('syncVideo');
    const startBtn = document.getElementById('startWatch');
    const socket = io("https://9ef6-2409-40d4-29-3e9a-3db8-6e12-3bb4-d2f9.ngrok-free.app"); // Replace this with your actual ngrok URL

    // --- Sync video events ---
    socket.emit('join-room', roomCode);

    startBtn.onclick = () => {
      socket.emit('play', roomCode, video.currentTime);
    };

    video.addEventListener('pause', () => {
      socket.emit('pause', roomCode, video.currentTime);
    });

    video.addEventListener('seeked', () => {
      socket.emit('seek', roomCode, video.currentTime);
    });

    socket.on('play', (time) => {
      video.currentTime = time;
      video.play();
    });

    socket.on('pause', (time) => {
      video.currentTime = time;
      video.pause();
    });

    socket.on('seek', (time) => {
      video.currentTime = time;
    });

    // --- WebRTC video chat ---
    const localVideo = document.getElementById('localVideo');
    const remoteVideo = document.getElementById('remoteVideo');
    const peerConfig = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] };

    let localStream;
    let peers = {};

    navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(stream => {
      localStream = stream;
      localVideo.srcObject = stream;
      socket.emit('ready-for-call', roomCode);
    });

    socket.on('user-joined', (id) => {
      if (id === socket.id) return;
      const peer = createPeer(id);
      peers[id] = peer;
    });

    socket.on('signal', async ({ from, signal }) => {
      let peer = peers[from];
      if (!peer) {
        peer = createPeer(from);
        peers[from] = peer;
      }

      if (signal.sdp) {
        await peer.setRemoteDescription(new RTCSessionDescription(signal.sdp));
        if (signal.sdp.type === 'offer') {
          const answer = await peer.createAnswer();
          await peer.setLocalDescription(answer);
          socket.emit('signal', {
            to: from,
            signal: { sdp: answer }
          });
        }
      } else if (signal.candidate) {
        await peer.addIceCandidate(new RTCIceCandidate(signal.candidate));
      }
    });

    function createPeer(id) {
      const peer = new RTCPeerConnection(peerConfig);

      localStream.getTracks().forEach(track => peer.addTrack(track, localStream));

      peer.onicecandidate = e => {
        if (e.candidate) {
          socket.emit('signal', {
            to: id,
            signal: { candidate: e.candidate }
          });
        }
      };

      peer.ontrack = e => {
        remoteVideo.srcObject = e.streams[0];
      };

      peer.createOffer().then(offer => {
        peer.setLocalDescription(offer);
        socket.emit('signal', {
          to: id,
          signal: { sdp: offer }
        });
      });

      return peer;
    }
  </script>
</body>
</html>
