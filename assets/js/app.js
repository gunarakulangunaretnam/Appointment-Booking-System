(function () {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
    var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var viewDate = new Date();
    var selectedDate = "";
    var markedDates = {};
    var cancelId = 0;

    function pad(n) {
        return n < 10 ? "0" + n : String(n);
    }

    function toDateString(date) {
        return date.getFullYear() + "-" + pad(date.getMonth() + 1) + "-" + pad(date.getDate());
    }

    function todayString() {
        return toDateString(new Date());
    }

    function parseMarked(grid) {
        if (!grid) {
            return {};
        }
        try {
            return JSON.parse(grid.getAttribute("data-marked") || "{}") || {};
        } catch (err) {
            return {};
        }
    }

    function showAlert(message, type) {
        var box = document.getElementById("formAlert");
        if (!box) {
            return;
        }
        box.className = "alert show " + (type || "success");
        box.textContent = message;
    }

    function api(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers["Content-Type"] = "application/json";
        options.headers["X-CSRF-Token"] = csrfToken;
        return fetch(url, options).then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok || data.success === false) {
                    throw new Error(data.error || "Request failed.");
                }
                return data;
            });
        });
    }

    function renderCalendar() {
        var grid = document.getElementById("calendarGrid");
        var title = document.getElementById("calendarTitle");
        if (!grid) {
            return;
        }

        var year = viewDate.getFullYear();
        var month = viewDate.getMonth();
        if (title) {
            title.textContent = monthNames[month] + " " + year;
        }

        var firstDay = new Date(year, month, 1).getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var html = "";
        var i;

        for (i = 0; i < firstDay; i += 1) {
            html += '<button class="day empty" type="button" disabled></button>';
        }

        for (i = 1; i <= daysInMonth; i += 1) {
            var value = year + "-" + pad(month + 1) + "-" + pad(i);
            var classes = "day";
            if (value < todayString()) {
                classes += " past";
            }
            if (markedDates[value]) {
                classes += " has-event";
            }
            if (selectedDate === value) {
                classes += " selected";
            }
            var disabled = (value < todayString() && document.getElementById("bookingForm")) ? " disabled" : "";
            html += '<button class="' + classes + '" type="button" data-date="' + value + '"' + disabled + ">" + i + "</button>";
        }

        grid.innerHTML = html;
    }

    function renderSlots(slots, targetId, selectedTime) {
        var wrap = document.getElementById(targetId || "slotList");
        if (!wrap) {
            return;
        }
        if (!slots || !slots.length) {
            wrap.innerHTML = '<p class="empty-state">No slots found.</p>';
            return;
        }

        wrap.innerHTML = slots.map(function (slot) {
            var classes = "slot";
            var disabled = !slot.available ? " disabled" : "";
            if (!slot.available) {
                classes += " booked";
            }
            if (selectedTime && selectedTime === slot.time && slot.available) {
                classes += " selected";
            }
            var label = slot.label + (slot.booked ? " (booked)" : "");
            return '<button class="' + classes + '" type="button" data-time="' + slot.time + '"' + disabled + ">" + label + "</button>";
        }).join("");
    }

    function loadSlots(date, targetId, selectedTime, excludeId) {
        if (!date) {
            return;
        }
        var url = "api/get_slots.php?date=" + encodeURIComponent(date);
        if (excludeId) {
            url += "&exclude_id=" + encodeURIComponent(excludeId);
        }
        return api(url).then(function (data) {
            renderSlots(data.slots, targetId, selectedTime);
            var label = document.getElementById("selectedDateLabel");
            if (label) {
                label.textContent = "Selected date: " + date;
            }
            var bookLink = document.getElementById("bookFromDashboard");
            if (bookLink) {
                bookLink.href = "book_appointment.php?date=" + encodeURIComponent(date);
            }
            return data;
        }).catch(function (err) {
            showAlert(err.message, "error");
        });
    }

    function selectDate(date, slotTarget, selectedTime, excludeId) {
        selectedDate = date;
        var dateField = document.getElementById("dateField");
        if (dateField) {
            dateField.value = date;
        }
        renderCalendar();
        loadSlots(date, slotTarget || "slotList", selectedTime, excludeId);
    }

    function bindCalendar(slotTarget) {
        var grid = document.getElementById("calendarGrid");
        if (!grid) {
            return;
        }
        markedDates = parseMarked(grid);

        document.getElementById("prevMonth") && document.getElementById("prevMonth").addEventListener("click", function () {
            viewDate.setMonth(viewDate.getMonth() - 1);
            renderCalendar();
        });
        document.getElementById("nextMonth") && document.getElementById("nextMonth").addEventListener("click", function () {
            viewDate.setMonth(viewDate.getMonth() + 1);
            renderCalendar();
        });

        grid.addEventListener("click", function (event) {
            var button = event.target.closest(".day");
            if (!button || button.disabled || !button.getAttribute("data-date")) {
                return;
            }
            var date = button.getAttribute("data-date");
            if (date < todayString() && document.getElementById("bookingForm")) {
                showAlert("Please choose today or a future date.", "error");
                return;
            }
            selectDate(date, slotTarget);
        });

        renderCalendar();
    }

    function bindSlotClicks(listId, hiddenId) {
        var list = document.getElementById(listId);
        var hidden = document.getElementById(hiddenId);
        if (!list || !hidden) {
            return;
        }
        list.addEventListener("click", function (event) {
            var button = event.target.closest(".slot");
            if (!button || button.disabled) {
                return;
            }
            var buttons = list.querySelectorAll(".slot");
            for (var i = 0; i < buttons.length; i += 1) {
                buttons[i].classList.remove("selected");
            }
            button.classList.add("selected");
            hidden.value = button.getAttribute("data-time");
        });
    }

    function initMenu() {
        var toggle = document.getElementById("menuToggle");
        var sidebar = document.getElementById("sidebar");
        var backdrop = document.getElementById("sidebarBackdrop");
        if (!toggle || !sidebar) {
            return;
        }
        function closeMenu() {
            sidebar.classList.remove("open");
            if (backdrop) {
                backdrop.classList.remove("show");
            }
        }
        toggle.addEventListener("click", function () {
            sidebar.classList.toggle("open");
            if (backdrop) {
                backdrop.classList.toggle("show");
            }
        });
        if (backdrop) {
            backdrop.addEventListener("click", closeMenu);
        }
    }

    function initBookingPage() {
        var form = document.getElementById("bookingForm");
        if (!form) {
            return;
        }
        bindCalendar("slotList");
        bindSlotClicks("slotList", "timeField");

        var params = new URLSearchParams(window.location.search);
        var preset = params.get("date") || todayString();
        var dateField = document.getElementById("dateField");
        dateField.value = preset;
        if (preset.length === 10) {
            viewDate = new Date(preset + "T00:00:00");
            selectDate(preset, "slotList");
        }

        dateField.addEventListener("change", function () {
            if (dateField.value) {
                viewDate = new Date(dateField.value + "T00:00:00");
                selectDate(dateField.value, "slotList");
            }
        });

        form.addEventListener("submit", function (event) {
            event.preventDefault();
            var payload = {
                title: document.getElementById("title").value,
                date: document.getElementById("dateField").value,
                time: document.getElementById("timeField").value,
                notes: document.getElementById("notes").value,
                csrf_token: csrfToken
            };
            if (!payload.time) {
                showAlert("Please select an available time slot.", "error");
                return;
            }
            api("api/create_appointment.php", {
                method: "POST",
                body: JSON.stringify(payload)
            }).then(function (data) {
                showAlert(data.message, "success");
                document.getElementById("title").value = "";
                document.getElementById("notes").value = "";
                document.getElementById("timeField").value = "";
                markedDates[payload.date] = (markedDates[payload.date] || 0) + 1;
                selectDate(payload.date, "slotList");
            }).catch(function (err) {
                showAlert(err.message, "error");
                if (payload.date) {
                    loadSlots(payload.date, "slotList");
                }
            });
        });
    }

    function initDashboardCalendar() {
        if (!document.getElementById("calendarGrid") || document.getElementById("bookingForm")) {
            return;
        }
        bindCalendar("slotList");
        selectDate(todayString(), "slotList");
    }

    function openModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.add("show");
        }
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove("show");
        }
    }

    function initAppointmentsPage() {
        var editForm = document.getElementById("editForm");
        if (!editForm) {
            return;
        }

        bindSlotClicks("editSlotList", "editTime");

        document.getElementById("activeTable").addEventListener("click", function (event) {
            var editBtn = event.target.closest(".js-edit");
            var cancelBtn = event.target.closest(".js-cancel");
            if (editBtn) {
                document.getElementById("editId").value = editBtn.getAttribute("data-id");
                document.getElementById("editTitle").value = editBtn.getAttribute("data-title");
                document.getElementById("editDate").value = editBtn.getAttribute("data-date");
                document.getElementById("editTime").value = editBtn.getAttribute("data-time");
                document.getElementById("editNotes").value = editBtn.getAttribute("data-notes") || "";
                loadSlots(
                    editBtn.getAttribute("data-date"),
                    "editSlotList",
                    editBtn.getAttribute("data-time"),
                    editBtn.getAttribute("data-id")
                );
                openModal("editModal");
            }
            if (cancelBtn) {
                cancelId = cancelBtn.getAttribute("data-id");
                document.getElementById("cancelMessage").textContent = 'Cancel "' + cancelBtn.getAttribute("data-title") + '"? This will free the time slot.';
                openModal("cancelModal");
            }
        });

        document.getElementById("editDate").addEventListener("change", function () {
            var date = document.getElementById("editDate").value;
            var id = document.getElementById("editId").value;
            document.getElementById("editTime").value = "";
            loadSlots(date, "editSlotList", "", id);
        });

        document.getElementById("closeEdit").addEventListener("click", function () {
            closeModal("editModal");
        });
        document.getElementById("closeCancel").addEventListener("click", function () {
            closeModal("cancelModal");
        });

        editForm.addEventListener("submit", function (event) {
            event.preventDefault();
            var payload = {
                id: parseInt(document.getElementById("editId").value, 10),
                title: document.getElementById("editTitle").value,
                date: document.getElementById("editDate").value,
                time: document.getElementById("editTime").value,
                notes: document.getElementById("editNotes").value,
                csrf_token: csrfToken
            };
            if (!payload.time) {
                showAlert("Please select a valid time slot.", "error");
                return;
            }
            api("api/update_appointment.php", {
                method: "POST",
                body: JSON.stringify(payload)
            }).then(function (data) {
                showAlert(data.message, "success");
                closeModal("editModal");
                window.location.reload();
            }).catch(function (err) {
                showAlert(err.message, "error");
                loadSlots(payload.date, "editSlotList", payload.time, payload.id);
            });
        });

        document.getElementById("confirmCancel").addEventListener("click", function () {
            api("api/cancel_appointment.php", {
                method: "POST",
                body: JSON.stringify({ id: parseInt(cancelId, 10), csrf_token: csrfToken })
            }).then(function (data) {
                showAlert(data.message, "success");
                closeModal("cancelModal");
                window.location.reload();
            }).catch(function (err) {
                showAlert(err.message, "error");
            });
        });
    }

    initMenu();
    initBookingPage();
    initDashboardCalendar();
    initAppointmentsPage();
}());
