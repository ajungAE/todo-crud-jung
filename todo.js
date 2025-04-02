/**
 * todo.js – Frontend-Logik für das ToDo-Listen-Projekt
 *
 * Dieses Skript kümmert sich um:
 * - Die Anzeige aller Aufgaben (via API-Fetch)
 * - Das Anlegen neuer Aufgaben über ein Formular
 * - Das Löschen einzelner Aufgaben
 * - Das Markieren von Aufgaben als erledigt oder wieder offen (Toggle)
 *
 * Die Kommunikation erfolgt per Fetch-API mit der PHP-Datei "todo-api.php",
 * welche die Verbindung zur MySQL-Datenbank herstellt.
 *
 * Alle Aufgaben werden dynamisch im DOM erstellt und aktualisiert,
 * ohne dass die Seite neu geladen werden muss.
 */



document.addEventListener('DOMContentLoaded', function () {

    // Define the URL to our CRUD server api
    const apiUrl = 'todo-api.php';

    // Create a delete button for each todo item
    const getDeleteButton = (item) => {
        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'Löschen';

        // Handle delete button click
        deleteButton.addEventListener('click', function () {
            fetch(apiUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: item.id })
            })
                .then(response => response.json())
                .then(() => {
                    fetchTodos(); // Reload todo list
                });
        });

        return deleteButton;
    }

    // create a complete button for each todo item
    const getCompleteButton = (item) => {
        const completeButton = document.createElement('button');
        completeButton.textContent = item.completed ? 'Wieder öffnen' : 'Erledigt';

        completeButton.addEventListener('click', function () {
            const newStatus = !item.completed; // toggle true/false

            fetch(apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: item.id, completed: newStatus })
            })
                .then(response => response.json())
                .then(() => {
                    item.completed = newStatus; // Zustand lokal aktualisieren

                    const parentLi = completeButton.parentElement;
                    if (newStatus) {
                        parentLi.style.textDecoration = 'line-through';
                        parentLi.style.opacity = '0.6';
                        completeButton.textContent = 'Wieder öffnen';
                    } else {
                        parentLi.style.textDecoration = 'none';
                        parentLi.style.opacity = '1';
                        completeButton.textContent = 'Erledigt';
                    }
                });
        });

        return completeButton;
    };


    // Create a delete button for each todo item
    const fetchTodos = () => {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                const todoList = document.getElementById('todo-list');
                todoList.innerHTML = "";
                data.forEach(Item => {
                    const item = {
                        ...Item,
                        completed: Item.completed === 1 || Item.completed === true
                    };

                    const li = document.createElement('li');
                    li.textContent = item.title;

                    if (item.completed) {
                        li.style.textDecoration = 'line-through'; // durchgestrichen
                        li.style.opacity = '0.6'; // ausgegraut
                    }

                    li.appendChild(getDeleteButton(item));
                    li.appendChild(getCompleteButton(item));
                    todoList.appendChild(li);
                }); // ← das war die fehlende Klammer hier
            });
    };


    // Handle form submit
    document.getElementById('todo-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const inputElement = document.getElementById('todo-input');
        const todoInput = inputElement.value;
        inputElement.value = "";


        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ title: todoInput })
        })
            .then(response => response.json())
            .then(data => {
                fetchTodos();
            });
    });

    // Load todos
    fetchTodos();

});