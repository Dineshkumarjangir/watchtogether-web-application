const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*",  // Update this with your Ngrok frontend URL if needed
    methods: ["GET", "POST"]
  }
});

io.on('connection', (socket) => {
  console.log("User connected:", socket.id);

  socket.on('join-room', (room) => {
    socket.join(room);
    console.log(`User joined room: ${room}`);
  });

  socket.on('start-video', (roomCode) => {
    io.to(roomCode).emit('start-video');
  });

  socket.on('play', (room, time) => {
    socket.to(room).emit('play', time);
  });

  socket.on('pause', (room, time) => {
    socket.to(room).emit('pause', time);
  });

  socket.on('seek', (room, time) => {
    socket.to(room).emit('seek', time);
  });
});

server.listen(3000, () => {
  console.log("Socket.IO server running at http://localhost:3000");
});
