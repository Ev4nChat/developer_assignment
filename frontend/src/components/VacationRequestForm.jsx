import React, { useState } from "react";
import api from "../api/axios";

const VacationRequestForm = ({ onRequestSuccess }) => {
    const [startDate, setStartDate] = useState("");
    const [endDate, setEndDate] = useState("");
    const [reason, setReason] = useState("");

    const today = new Date().toISOString().split("T")[0]; // Today's date in YYYY-MM-DD format

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            await api.post(
                "/api/vacation_requests",
                {
                    startDate,
                    endDate,
                    reason
                },
                {
                    headers: {
                        'Content-Type': 'application/ld+json' // Required by API Platform
                    },
                    withCredentials: true
                }
            );

            onRequestSuccess?.(); // Optional chaining in case callback is not passed
            setStartDate("");
            setEndDate("");
            setReason("");
        } catch (error) {
            console.error("Error submitting vacation request:", error);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <h2>Submit Vacation Request</h2>
            <div>
                <label>Start Date:</label>
                <input
                    type="date"
                    value={startDate}
                    onChange={(e) => setStartDate(e.target.value)}
                    required
                    min={today} // Prevent past dates
                />
            </div>

            <div>
                <label>End Date:</label>
                <input
                    type="date"
                    value={endDate}
                    onChange={(e) => setEndDate(e.target.value)}
                    required
                    min={today} // Prevent past dates
                />
            </div>

            <div>
                <label>Reason:</label>
                <input
                    type="text"
                    value={reason}
                    onChange={(e) => setReason(e.target.value)}
                    required
                />
            </div>

            <button type="submit">Submit Request</button>
        </form>
    );
};

export default VacationRequestForm;
