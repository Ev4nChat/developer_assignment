import React, { useEffect, useState, useContext } from 'react';
import api from '../api/axios';
import VacationRequestForm from '../components/VacationRequestForm';
import AuthContext from '../context/AuthContext';

const VacationRequestList = ({ requests, onDelete }) => {
    const formatDate = (date) => new Date(date).toLocaleDateString();

    return (
        <table className="styled-table">
            <thead>
            <tr>
                <th>Submission Date</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            {requests.map((request) => (
                <tr key={request.id}>
                    <td>{formatDate(request.submittedAt)}</td>
                    <td>{formatDate(request.startDate)}</td>
                    <td>{formatDate(request.endDate)}</td>
                    <td>{request.status}</td>
                    <td>
                        {/* Show Delete button only if the status is 'Pending' */}
                        {request.status === 'Pending' && (
                            <button
                                onClick={() => onDelete(request.id)}
                                className="delete-button"
                            >
                                Delete
                            </button>
                        )}
                    </td>
                </tr>
            ))}
            </tbody>
        </table>
    );
};

const EmployeeDashboard = () => {
    const { logout } = useContext(AuthContext);
    const [vacationRequests, setVacationRequests] = useState([]);
    const [showFormModal, setShowFormModal] = useState(false);

    const fetchRequests = async () => {
        try {
            const response = await api.get('/api/vacation_requests');
            setVacationRequests(response.data.member);
        } catch (error) {
            console.error('Error fetching vacation requests:', error);
        }
    };

    useEffect(() => {
        fetchRequests();
    }, []);

    const handleDelete = async (id) => {
        try {
            await api.delete(`/api/vacation_requests/${id}/delete`);
            fetchRequests();
        } catch (error) {
            console.error('Error deleting vacation request:', error);
        }
    };

    const handleRequestSuccess = () => {
        fetchRequests();
        setShowFormModal(false);
    };

    return (
        <div className="dashboard-container">
            <style>{`
                .dashboard-container {
                    max-width: 1000px;
                    margin: 0 auto;
                    padding: 20px;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                }

                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 30px;
                }

                .dashboard-title {
                    font-size: 28px;
                    color: #2c3e50;
                }

                .logout-button {
                    background-color: #7f8c8d;
                    color: white;
                    border: none;
                    padding: 10px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }

                .logout-button:hover {
                    background-color: #636e72;
                }

                .section {
                    background-color: #fdfdfd;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
                    margin-bottom: 30px;
                }

                .create-button {
                    background-color: #3498db;
                    color: white;
                    border: none;
                    padding: 10px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                    margin-bottom: 20px;
                }

                .create-button:hover {
                    background-color: #2980b9;
                }

                .styled-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                }

                .styled-table th, .styled-table td {
                    padding: 12px 16px;
                    text-align: left;
                }

                .styled-table thead {
                    background-color: #ecf0f1;
                    font-weight: bold;
                }

                .styled-table tbody tr:nth-child(even) {
                    background-color: #f9f9f9;
                }

                .styled-table tbody tr:hover {
                    background-color: #f1f1f1;
                }

                .delete-button {
                    background-color: #e74c3c;
                    color: white;
                    border: none;
                    padding: 8px 14px;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }

                .delete-button:hover {
                    background-color: #c0392b;
                }

                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    background-color: rgba(0, 0, 0, 0.4);
                    backdrop-filter: blur(3px);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                }

                .modal-content {
                    background-color: white;
                    padding: 30px;
                    border-radius: 10px;
                    width: 100%;
                    max-width: 500px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                    position: relative;
                    animation: slideDown 0.3s ease-out;
                }

                @keyframes slideDown {
                    from {
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .modal-close {
                    position: absolute;
                    top: 10px;
                    right: 14px;
                    background: none;
                    border: none;
                    font-size: 24px;
                    font-weight: bold;
                    cursor: pointer;
                    color: #888;
                    transition: color 0.2s ease;
                }

                .modal-close:hover {
                    color: #333;
                }
            `}</style>

            <div className="header">
                <h1 className="dashboard-title">Employee Dashboard</h1>
                <button onClick={logout} className="logout-button">
                    Logout
                </button>
            </div>

            <div className="section">
                <button className="create-button" onClick={() => setShowFormModal(true)}>
                    Create Vacation Request
                </button>

                <VacationRequestList
                    requests={vacationRequests}
                    onDelete={handleDelete}
                />
            </div>

            {showFormModal && (
                <div className="modal-overlay">
                    <div className="modal-content">
                        <button className="modal-close" onClick={() => setShowFormModal(false)}>
                            &times;
                        </button>
                        <VacationRequestForm onRequestSuccess={handleRequestSuccess} />
                    </div>
                </div>
            )}
        </div>
    );
};

export default EmployeeDashboard;