
const { createServer } = require("http");
const { Server } = require("socket.io");
const Redis = require("ioredis");

const httpServer = createServer();
const io = new Server(httpServer, {
    cors: { origin: "*" }
});

const redis = new Redis(); // Redis عادي

io.on("connection", (socket) => {
    console.log("Client connected:", socket.id);
    socket.onAny((event, ...args) => {
        console.log(`Event received: ${event}`, args);
    });
    socket.on("join-room", (room) => {
        socket.join(room);
        console.log(`Socket ${socket.id} joined ${room}`);
    });

    // // Manually emit an event for testing purposes
    // setInterval(() => {
    //     const testEventData = {
    //         event: "new-order-created",
    //         data: {
    //             orderId: 1234,
    //             customerName: "John Doe",
    //             items: [
    //                 { name: "Item 1", quantity: 2 },
    //                 { name: "Item 2", quantity: 1 }
    //             ]
    //         }
    //     };
    //
    //     // Publishing the event to Redis (in your example, we send it to a specific channel)
    //     redis.publish("new-order-created", JSON.stringify(testEventData));
    // }, 5000); // Send an event every 5 seconds for testing
});

redis.psubscribe("*", () => {});

redis.on("pmessage", (pattern, channel, message) => {
    console.log('Received message from Redis');
    const parsed = JSON.parse(message);
    const eventName = parsed.event;
    const data = parsed.data;

    console.log(eventName)

    // Room name = channel name
    io.to(eventName).emit(eventName, data);
});

httpServer.listen(6001, () => {
    console.log("Socket.IO server listening on port 6001");
});
