document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const chatMessages = document.getElementById('chat-messages');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    const filePreviewImage = document.getElementById('file-preview-image');
    const filePreviewName = document.getElementById('file-preview-name');
    const filePreviewSize = document.getElementById('file-preview-size');
    const filePreviewClear = document.getElementById('file-preview-clear');
    const onlineUsersList = document.getElementById('online-users-list');
    const mobileOnlineUsersList = document.getElementById('mobile-online-users-list');
    const messageTemplate = document.getElementById('message-template');
    const toggleSidebarBtn = document.getElementById('toggle-sidebar');
    const attachFileBtn = document.getElementById('attach-file-btn');
    
    // Variables
    let lastMessageId = 0;
    let messageCheckInterval;

    // Initialize chat
    function initChat() {
        console.log('Initializing chat...');
        
        // Fix viewport height for mobile
        setMobileViewportHeight();
        window.addEventListener('resize', setMobileViewportHeight);
        
        // Setup mobile sidebar
        setupMobileSidebar();
        
        // Load initial messages
        getMessages();
        
        // Set up polling for new messages (every 3 seconds)
        messageCheckInterval = setInterval(checkNewMessages, 3000);
        
        // Set up polling for online users (every 30 seconds)
        setInterval(updateOnlineUsers, 30000);
        
        // Handle message submission
        if (messageForm) {
            messageForm.addEventListener('submit', sendMessage);
        }
        
        // Mobile sidebar toggle
        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', toggleSidebar);
        }
        
        // Manually find and set up the file input directly
        const fileInputElement = document.getElementById('file-input');
        console.log('File input found:', !!fileInputElement);
        
        if (fileInputElement) {
            console.log('Setting up file input change event');
            // Remove any existing handlers
            fileInputElement.removeEventListener('change', handleFileInputChange);
            // Add new handler
            fileInputElement.addEventListener('change', function(event) {
                console.log('File input change event fired');
                handleFileInputChange(event);
            });
        } else {
            console.error('File input element not found during initialization');
        }
        
        // Set up file clear button
        const clearButton = document.getElementById('file-preview-clear');
        if (clearButton) {
            clearButton.addEventListener('click', clearFilePreview);
        }
        
        // Set up attach file button
        const attachButton = document.getElementById('attach-file-btn');
        if (attachButton && fileInputElement) {
            attachButton.addEventListener('click', function() {
                console.log('Attach file button clicked');
                fileInputElement.click();
            });
        } else {
            console.error('Attach button or file input not found');
        }
        
        // Scroll to bottom initially
        setTimeout(scrollToBottom, 500);
        
        // Fix for mobile keyboards
        if (messageInput) {
            messageInput.addEventListener('focus', scrollToBottom);
        }
        
        console.log('Chat initialized successfully');
    }

    // Toggle mobile sidebar
    function toggleSidebar() {
        console.log('Toggle sidebar clicked');
        const mobileSidebar = document.querySelector('.mobile-sidebar');
        if (mobileSidebar) {
            mobileSidebar.classList.toggle('show');
        } else {
            console.log('Mobile sidebar element not found');
            // Try with the old sidebar if mobile one doesn't exist
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        }
    }
    
    // Close sidebar when clicking outside
    function setupMobileSidebar() {
        const mobileSidebar = document.querySelector('.mobile-sidebar');
        const closeSidebarBtn = document.getElementById('close-sidebar');
        
        if (mobileSidebar) {
            // Close when clicking on backdrop
            mobileSidebar.addEventListener('click', function(e) {
                if (e.target === mobileSidebar) {
                    mobileSidebar.classList.remove('show');
                }
            });
            
            // Close button
            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener('click', function() {
                    mobileSidebar.classList.remove('show');
                });
            }
        }
    }
    
    // Fix for mobile viewport height
    function setMobileViewportHeight() {
        // First we get the viewport height and multiply it by 1% to get a value for a vh unit
        let vh = window.innerHeight * 0.01;
        // Then we set the value in the --vh custom property to the root of the document
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    // Get all messages
    function getMessages() {
        fetch('../chat/get_messages.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear loading indicator
                    chatMessages.innerHTML = '';
                    
                    // Add messages to the chat
                    data.data.forEach(message => {
                        appendMessage(message);
                    });
                    
                    // Update last message ID
                    if (data.data.length > 0) {
                        lastMessageId = Math.max(...data.data.map(m => m.id));
                    }
                    
                    // Scroll to bottom
                    scrollToBottom();
                }
            })
            .catch(error => {
                console.error('Error fetching messages:', error);
            });
    }
    
    // Check for new messages
    function checkNewMessages() {
        if (lastMessageId > 0) {
            fetch(`../chat/get_messages.php?after_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        // Add new messages to the chat
                        data.data.forEach(message => {
                            appendMessage(message);
                        });
                        
                        // Update last message ID
                        lastMessageId = Math.max(...data.data.map(m => m.id));
                        
                        // Scroll to bottom
                        scrollToBottom();
                    }
                })
                .catch(error => {
                    console.error('Error checking for new messages:', error);
                });
        }
    }
    
    // Handle file input change
    function handleFileInputChange(event) {
        console.log('File input change detected');
        if (!event || !event.target) {
            console.error('Event or event.target is undefined');
            return;
        }
        
        const file = event.target.files[0];
        if (!file) {
            console.log('No file selected');
            return;
        }
        
        console.log('File selected:', file.name, file.type, file.size);
        
        // Check file size (max 10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            alert('File size too large. Maximum allowed size is 10MB.');
            event.target.value = '';
            return;
        }
        
        // Update file preview
        if (filePreviewName) filePreviewName.textContent = file.name;
        if (filePreviewSize) filePreviewSize.textContent = formatFileSize(file.size);
        
        // Show image preview if it's an image
        if (file.type.startsWith('image/') && filePreviewImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                filePreviewImage.src = e.target.result;
                filePreviewImage.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else if (filePreviewImage) {
            filePreviewImage.style.display = 'none';
        }
        
        // Show file preview container
        if (filePreview) filePreview.style.display = 'block';
        
        // Log success
        console.log('File preview updated successfully');
    }
    
    // Clear file preview
    function clearFilePreview() {
        console.log('Clearing file preview');
        const fileInputElement = document.getElementById('file-input');
        
        if (fileInputElement) {
            fileInputElement.value = '';
        }
        
        if (filePreview) {
            filePreview.style.display = 'none';
        }
        
        if (filePreviewImage) {
            filePreviewImage.src = '';
        }
        
        if (filePreviewName) {
            filePreviewName.textContent = '';
        }
        
        if (filePreviewSize) {
            filePreviewSize.textContent = '';
        }
    }
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Send a new message
    function sendMessage(event) {
        event.preventDefault();
        console.log('Send message triggered');
        
        const message = messageInput.value.trim();
        const fileInputElement = document.getElementById('file-input');
        const hasFile = fileInputElement && fileInputElement.files && fileInputElement.files.length > 0;
        
        console.log('Message:', message);
        console.log('File input element found:', !!fileInputElement);
        console.log('Has file:', hasFile);
        
        // Check if we have either message or file
        if (!message && !hasFile) {
            console.log('No message or file to send');
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('message', message);
        
        // Add file if selected
        if (hasFile) {
            const file = fileInputElement.files[0];
            console.log('Attaching file:', file.name, file.type, file.size);
            formData.append('file', file);
        }
        
        // Clear inputs
        messageInput.value = '';
        if (hasFile) {
            clearFilePreview();
        }
        
        // Disable form while sending
        const submitButton = messageForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        }
        
        // Log form data for debugging
        if (hasFile) {
            console.log('FormData contains file:', formData.has('file'));
            console.log('File in FormData:', formData.get('file').name);
        }
        
        // Send message - use fetch with a longer timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
        
        fetch('../chat/send_message.php', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        })
            .then(response => {
                clearTimeout(timeoutId);
                console.log('Response received:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Message sent, response:', data);
                if (data.success) {
                    // Message sent successfully
                    appendMessage(data.data);
                    
                    // Update last message ID
                    lastMessageId = data.data.id;
                    
                    // Scroll to bottom
                    scrollToBottom();
                } else {
                    // Show error message
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
            })
            .finally(() => {
                // Re-enable form
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="bi bi-send-fill"></i>';
                }
            });
    }
    
    // Append a message to the chat
    function appendMessage(message) {
        // Clone the template
        const messageNode = messageTemplate.content.cloneNode(true);
        
        // Set message class based on sender
        const messageDiv = messageNode.querySelector('.message');
        if (parseInt(message.user_id) === currentUser.id) {
            messageDiv.classList.add('own');
        } else {
            messageDiv.classList.add('other');
        }
        
        // Set profile picture path
        let profilePicPath = '../assets/img/' + message.profile_pic;
        
        // Check if it's a custom profile pic
        if (message.profile_pic && message.profile_pic !== 'default-avatar.png') {
            // Try the uploaded profile pic path first
            const uploadedPath = '../uploads/profile/' + message.profile_pic;
            profilePicPath = uploadedPath;
        }
        
        // Add a timestamp parameter to prevent browser caching
        profilePicPath += '?t=' + new Date().getTime();
        
        // Set message content
        messageNode.querySelector('.avatar').src = profilePicPath;
        messageNode.querySelector('.avatar').alt = message.username;
        messageNode.querySelector('.username').textContent = message.username;
        messageNode.querySelector('.timestamp').textContent = message.created_at;
        
        // Set message text if not empty
        const messageBody = messageNode.querySelector('.message-body');
        if (message.message && message.message.trim()) {
            messageBody.textContent = message.message;
        } else if (!message.has_file) {
            // If no message and no file, add a placeholder
            messageBody.textContent = '...';
        }
        
        // Add file attachment if present
        if (message.has_file) {
            const attachmentDiv = document.createElement('div');
            attachmentDiv.className = 'message-attachment';
            
            // Create icon
            const iconDiv = document.createElement('div');
            iconDiv.className = 'attachment-icon';
            
            let iconClass = 'bi-file-earmark';
            if (message.file_type.startsWith('image/')) {
                iconClass = 'bi-file-image';
            } else if (message.file_type.startsWith('video/')) {
                iconClass = 'bi-file-play';
            } else if (message.file_type.startsWith('audio/')) {
                iconClass = 'bi-file-music';
            } else if (message.file_type.includes('pdf')) {
                iconClass = 'bi-file-pdf';
            } else if (message.file_type.includes('word') || message.file_type.includes('document')) {
                iconClass = 'bi-file-word';
            } else if (message.file_type.includes('sheet') || message.file_type.includes('excel')) {
                iconClass = 'bi-file-excel';
            } else if (message.file_type.includes('zip') || message.file_type.includes('rar')) {
                iconClass = 'bi-file-zip';
            }
            
            iconDiv.innerHTML = `<i class="bi ${iconClass}"></i>`;
            attachmentDiv.appendChild(iconDiv);
            
            // Create info
            const infoDiv = document.createElement('div');
            infoDiv.className = 'attachment-info';
            
            const nameDiv = document.createElement('div');
            nameDiv.className = 'attachment-name';
            nameDiv.textContent = message.original_file_name || message.file_name;
            infoDiv.appendChild(nameDiv);
            
            const sizeDiv = document.createElement('div');
            sizeDiv.className = 'attachment-size';
            sizeDiv.textContent = message.formatted_file_size || formatFileSize(message.file_size);
            infoDiv.appendChild(sizeDiv);
            
            attachmentDiv.appendChild(infoDiv);
            
            // Create download link
            const downloadLink = document.createElement('a');
            // Use file_url (absolute) if available, otherwise fall back to file_path (relative)
            downloadLink.href = message.file_url || message.file_path;
            downloadLink.className = 'btn btn-sm btn-primary ms-2';
            downloadLink.download = message.original_file_name || message.file_name;
            downloadLink.innerHTML = '<i class="bi bi-download"></i>';
            downloadLink.title = 'Download';
            downloadLink.target = '_blank';
            attachmentDiv.appendChild(downloadLink);
            
            messageBody.appendChild(attachmentDiv);
            
            // Add preview for images, videos, and audio
            if (message.file_type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = message.file_url || message.file_path;
                img.alt = message.original_file_name || message.file_name;
                img.className = 'attachment-preview';
                img.loading = 'lazy';
                messageBody.appendChild(img);
            } else if (message.file_type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = message.file_url || message.file_path;
                video.className = 'attachment-preview';
                video.controls = true;
                messageBody.appendChild(video);
            } else if (message.file_type.startsWith('audio/')) {
                const audio = document.createElement('audio');
                audio.src = message.file_url || message.file_path;
                audio.className = 'w-100 mt-2';
                audio.controls = true;
                messageBody.appendChild(audio);
            }
        }
        
        // Append to chat
        chatMessages.appendChild(messageNode);
        
        // Clean up (remove messages if there are too many)
        const messages = chatMessages.querySelectorAll('.message');
        if (messages.length > 100) {
            for (let i = 0; i < messages.length - 100; i++) {
                chatMessages.removeChild(messages[i]);
            }
        }
    }
    
    // Scroll chat to bottom
    function scrollToBottom() {
        console.log('Scrolling to bottom...');
        if (chatMessages) {
            // For mobile iOS Safari, need to handle differently
            if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
                // Sometimes iOS needs a small delay
                setTimeout(function() {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 100);
            } else {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }
    }
    
    // Update online users list
    function updateOnlineUsers() {
        fetch('../chat/get_online_users.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the online users list
                    if (onlineUsersList) {
                        onlineUsersList.innerHTML = '';
                        
                        data.data.forEach(user => {
                            const listItem = document.createElement('li');
                            listItem.className = 'list-group-item d-flex align-items-center';
                            
                            // Set profile picture path with cache-busting parameter
                            let profilePicPath = '../assets/img/' + user.profile_pic;
                            if (user.profile_pic && user.profile_pic !== 'default-avatar.png') {
                                profilePicPath = '../uploads/profile/' + user.profile_pic;
                            }
                            
                            const img = document.createElement('img');
                            img.src = profilePicPath + '?t=' + new Date().getTime();
                            img.alt = user.username;
                            img.className = 'rounded-circle me-2';
                            img.width = 32;
                            img.height = 32;
                            
                            const span = document.createElement('span');
                            span.textContent = user.username;
                            
                            listItem.appendChild(img);
                            listItem.appendChild(span);
                            onlineUsersList.appendChild(listItem);
                        });
                    }
                    
                    // Also update mobile users list if it exists
                    if (mobileOnlineUsersList) {
                        mobileOnlineUsersList.innerHTML = '';
                        
                        data.data.forEach(user => {
                            const listItem = document.createElement('li');
                            listItem.className = 'list-group-item d-flex align-items-center';
                            
                            // Set profile picture path
                            let profilePicPath = '../assets/img/' + user.profile_pic;
                            if (user.profile_pic && user.profile_pic !== 'default-avatar.png') {
                                profilePicPath = '../uploads/profile/' + user.profile_pic;
                            }
                            
                            const img = document.createElement('img');
                            img.src = profilePicPath + '?t=' + new Date().getTime();
                            img.alt = user.username;
                            img.className = 'rounded-circle me-2';
                            img.width = 32;
                            img.height = 32;
                            
                            const span = document.createElement('span');
                            span.textContent = user.username;
                            
                            listItem.appendChild(img);
                            listItem.appendChild(span);
                            mobileOnlineUsersList.appendChild(listItem);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error updating online users:', error);
            });
    }
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        clearInterval(messageCheckInterval);
    });
    
    // Initialize the chat
    initChat();
});