async function addNote() {
    const noteText = document.getElementById('noteText').value;
    if (!noteText.trim()) {
        alert('Please enter a note before saving.');
        return;
    }

    try {
        const response = await fetch('server.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&note=${encodeURIComponent(noteText)}`
        });

        if (!response.ok) {
            throw new Error('Failed to save note');
        }

        await response.text();
        document.getElementById('noteText').value = '';
        getAllNotes(); // Refresh the notes list
        alert('Note saved successfully!');
    } catch (error) {
        alert('Error saving note: ' + error.message);
    }
}

async function getAllNotes() {
    try {
        const response = await fetch('server.php?action=get');
        if (!response.ok) {
            throw new Error('Failed to fetch notes');
        }

        const notes = await response.json();
        const notesList = document.getElementById('notesList');
        notesList.innerHTML = ''; // This is safe as we're clearing the container

        notes.forEach(note => {
            const noteElement = document.createElement('div');
            noteElement.className = 'note';

            // Create content div
            const contentDiv = document.createElement('div');
            contentDiv.className = 'note-content';
            contentDiv.innerText = note.content;

            // Create date div
            const dateDiv = document.createElement('div');
            dateDiv.className = 'note-date';
            dateDiv.innerText = new Date(note.created_at).toLocaleString();

            // Append both divs to the note element
            noteElement.appendChild(contentDiv);
            noteElement.appendChild(dateDiv);

            // Append the note element to the list
            notesList.appendChild(noteElement);
        });
    } catch (error) {
        alert('Error fetching notes: ' + error.message);
    }
}

// Load notes when the page loads
document.addEventListener('DOMContentLoaded', getAllNotes);
