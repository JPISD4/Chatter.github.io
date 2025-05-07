<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Chat Room</title>
  <!-- Bootstrap for styling -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <!-- jQuery for AJAX calls -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <style>
    body { background: #f8f9fa; }
    .chat-container {
      margin: 50px auto;
      max-width: 800px;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    .status-bar {
      margin-bottom: 15px;
      font-size: 0.9em;
      color: #555;
    }
    .messages {
      height: 300px;
      overflow-y: scroll;
      border: 1px solid #dee2e6;
      padding: 10px;
      margin-bottom: 15px;
    }
    .message { padding: 8px; border-bottom: 1px solid #f1f1f1; }
    .message:last-child { border-bottom: none; }
    .message strong { color: #007bff; }
    .timestamp { font-size: 0.8em; color: #6c757d; }
  </style>
</head>
<body>
  <div class="chat-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>Chat Room</h4>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
    <!-- Online/typing status area -->
    <div class="status-bar" id="userStatus">
      <!-- User statuses updated via AJAX -->
    </div>
    <div class="messages">
      <!-- Chat messages are loaded here via AJAX -->
    </div>
    <form id="chatForm">
      <div class="form-group">
        <textarea name="message" id="messageInput" class="form-control" placeholder="Type your message..." rows="3" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Send</button>
    </form>
  </div>
  
  <script>
    // Refresh chat messages every 2 seconds
    function refreshMessages() {
      $.ajax({
        url: 'messages.php',
        method: 'GET',
        cache: false,
        success: function(data) {
          var messagesDiv = $('.messages');
          messagesDiv.html(data);
          messagesDiv.scrollTop(messagesDiv[0].scrollHeight);
        },
        error: function(xhr, status, error) {
          console.error("Error fetching messages:", error);
        }
      });
    }
    
    // Update current user's status; typing is true or false.
    function updateUserStatus(typing) {
      $.ajax({
        url: 'update_status.php',
        method: 'POST',
        data: { typing: typing ? "1" : "0" },
        cache: false,
        error: function(xhr, status, error) {
          console.error("Error updating status:", error);
        }
      });
    }
    
    // Refresh online/typing status indicator.
    function refreshUserStatus() {
      $.ajax({
        url: 'get_status.php',
        method: 'GET',
        cache: false,
        success: function(data) {
          $('#userStatus').html(data);
        },
        error: function(xhr, status, error) {
          console.error("Error fetching user status:", error);
        }
      });
    }
    
    // Function to edit a message using a prompt.
    function editMessage(msgId, currentText) {
      var newText = prompt("Edit your message:", currentText);
      if(newText !== null && newText.trim() !== "" && newText !== currentText) {
        $.ajax({
          url: 'edit_message.php',
          method: 'POST',
          data: { id: msgId, new_message: newText },
          success: function(response) {
            // Optionally, you can display response feedback.
            refreshMessages();
          },
          error: function(xhr, status, error) {
            console.error("Error editing message:", error);
          }
        });
      }
    }
    
    $(document).ready(function() {
      // Load messages and status immediately.
      refreshMessages();
      refreshUserStatus();
      
      // Poll for messages and status every 2 seconds.
      setInterval(refreshMessages, 2000);
      setInterval(refreshUserStatus, 2000);
      
      var typingTimer;
      var doneTypingInterval = 1000; // Trigger "stopped typing" after 1 sec
      
      // When a key is pressed in the textarea, update typing status.
      $('#messageInput').on('keydown', function(e) {
        clearTimeout(typingTimer);
        updateUserStatus(true);
        // If Enter is pressed (without Shift) then submit the form.
        if(e.which === 13 && !e.shiftKey) {
          e.preventDefault();
          $("#chatForm").submit();
        }
      });
      
      // When keys are released, update the typing status after a short delay.
      $('#messageInput').on('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function() {
          updateUserStatus(false);
        }, doneTypingInterval);
      });
      
      // Submit the message via AJAX.
      $("#chatForm").on("submit", function(e) {
        e.preventDefault();
        updateUserStatus(false); // Clear typing status when message is sent.
        var messageData = $(this).serialize();
        $.ajax({
          url: 'send_message.php',
          method: 'POST',
          data: messageData,
          success: function(response) {
            $("#chatForm textarea").val('');
            refreshMessages();
          },
          error: function(xhr, status, error) {
            console.error("Error sending message:", error);
          }
        });
      });
      
      // Update status periodically to keep the user's "last active" fresh.
      setInterval(function() {
        updateUserStatus(false);
      }, 10000); // every 10 seconds
    });
  </script>
</body>
</html>
