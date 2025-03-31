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
        completeButton.textContent = 'Erledigt';

        completeButton.addEventListener('click', function () {
            fetch(apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: item.id, completed: true })
            })
                .then(response => response.json())
                .then(updatedItem => { // (NEW)
                    const parentLi = completeButton.parentElement;// (NEW)
                    parentLi.style.textDecoration = 'line-through';// (NEW)
                    parentLi.style.opacity = '0.6';// (NEW)
                });
        });

        return completeButton;
    }

    // Create a delete button for each todo item
    const fetchTodos = () => {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                const todoList = document.getElementById('todo-list');
                todoList.innerHTML = "";
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item.title;
                    // check if completed (NEW)
                    if (item.completed) {
                        li.style.textDecoration = 'line-through'; // für durchgestrichenen Look
                        li.style.opacity = '0.6'; // für ausgegrauten Look
                    }
                    li.appendChild(getDeleteButton(item));
                    li.appendChild(getCompleteButton(item)); // Complete Button (NEW)
                    todoList.appendChild(li);
                });
            });
    }

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
                const todoList = document.getElementById('todo-list');
                const li = document.createElement('li');
                li.textContent = data.title;
                li.appendChild(getDeleteButton(data));
                li.appendChild(getCompleteButton(data)); // ← ADDING Complete BUTTON (NEW)
                todoList.appendChild(li);
            });
    });

    // Load todos
    fetchTodos();

});