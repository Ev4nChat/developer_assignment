import React, { useEffect, useState, useContext } from 'react';
import api from '../api/axios';
import ConfirmModal from '../components/ConfirmModal';
import HandleUserModal from '../components/HandleUserModal.jsx';
import AuthContext from '../context/AuthContext';

const ManagerDashboard = () => {
    const { logout } = useContext(AuthContext);
    const [users, setUsers] = useState([]);
    const [vacationRequests, setVacationRequests] = useState([]);
    const [showConfirmModal, setShowConfirmModal] = useState(false);
    const [selectedUserId, setSelectedUserId] = useState(null);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);
    const [notification, setNotification] = useState(null); // New notification state

    const fetchUsers = async () => {
        try {
            const response = await api.get('/api/users');
            setUsers(response.data.member);
        } catch (error) {
            console.error('Error fetching users:', error);
        }
    };

    const fetchVacationRequests = async () => {
        try {
            const response = await api.get('/api/vacation_requests');
            setVacationRequests(response.data.member);
        } catch (error) {
            console.error('Error fetching vacation requests:', error);
        }
    };

    useEffect(() => {
        fetchUsers();
        fetchVacationRequests();
    }, []);

    useEffect(() => {
        if (notification) {
            const timeout = setTimeout(() => setNotification(null), 4000);
            return () => clearTimeout(timeout);
        }
    }, [notification]);

    const handleDeleteUser = (userId) => {
        setSelectedUserId(userId);
        setShowConfirmModal(true);
    };

    const confirmDelete = async () => {
        try {
            await api.delete(`/api/users/${selectedUserId}`);

            setUsers(prev => prev.filter(user => user.id !== selectedUserId));
            setVacationRequests(prev => prev.filter(req => req.user.id !== selectedUserId));

            setNotification('User deleted successfully.');
        } catch (error) {
            console.error('Error deleting user:', error);
            setNotification('Failed to delete user.');
        } finally {
            setShowConfirmModal(false);
            setSelectedUserId(null);
        }
    };

    const cancelDelete = () => {
        setShowConfirmModal(false);
        setSelectedUserId(null);
    };

    const handleCreateUser = async (newUserData) => {
        try {
            await api.post('/api/users', newUserData, {
                headers: { 'Content-Type': 'application/ld+json' },
            });
            fetchUsers();
            setNotification('User created successfully.');
        } catch (error) {
            console.error('Error creating user:', error);
            if (error.response?.status === 422 && error.response?.data?.violations) {
                const firstViolation = error.response.data.violations[0];
                const readableField = firstViolation.propertyPath
                    .replace(/([A-Z])/g, ' $1')      // Add space before capital letters
                    .replace(/^./, str => str.toUpperCase()); // Capitalize first letter
                setNotification(`Failed to create user: ${readableField} ${firstViolation.message}`);
            } else if (error.response?.status === 409 || error.response?.status === 400) {
                setNotification('Failed to create user: Email is already in use.');
            } else {
                setNotification('Failed to create user due to server error.');
            }
        } finally {
            setShowCreateModal(false);
            setSelectedUser(null);
        }
    };

    const handleEditUser = (user) => {
        setSelectedUser(user);
        setShowCreateModal(true);
    };

    const handleUpdateUser = async (updatedUserData) => {
        try {
            await api.patch(`/api/users/${selectedUser.id}`, updatedUserData, {
                headers: { 'Content-Type': 'application/merge-patch+json' },
            });
            fetchUsers();
            setNotification('User updated successfully.');
        } catch (error) {
            console.error('Error updating user:', error);
            setNotification('Failed to update user.');
        } finally {
            setShowCreateModal(false);
            setSelectedUser(null);
        }
    };

    const handleApprove = async (requestId) => {
        try {
            await api.post(`/api/vacation_requests/${requestId}/approve`);
            fetchVacationRequests();
            setNotification('Request approved.');
        } catch (error) {
            console.error('Error approving request:', error);
            setNotification('Failed to approve request.');
        }
    };

    const handleReject = async (requestId) => {
        try {
            await api.post(`/api/vacation_requests/${requestId}/reject`);
            fetchVacationRequests();
            setNotification('Request rejected.');
        } catch (error) {
            console.error('Error rejecting request:', error);
            setNotification('Failed to reject request.');
        }
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
                    box-shadow: 0 2px 6px rgba( 0, 0, 0, 0.04);
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

                .actions-column {
                    display: flex;
                    gap: 10px;
                }

                .delete-button {
                    background-color: #e74c3c;
                    color: white;
                    border: none;
                    padding: 10px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                    margin-bottom: 20px;
                }

                .delete-button:hover {
                    background-color: #c0392b;
                }

                .approve-button {
                    background-color: #2ecc71;
                    color: white;
                    border: none;
                    padding: 8px 14px;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }

                .approve-button:hover {
                    background-color: #27ae60;
                }

                .reject-button {
                    background-color: #e74c3c;
                    color: white;
                    border: none;
                    padding: 8px 14px;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }

                .reject-button:hover {
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
                <h1 className="dashboard-title">Manager Dashboard</h1>
                <button onClick={logout} className="logout-button">
                    Logout
                </button>
            </div>

            {/* Notification Box */}
            {notification && (
                <div style={{
                    backgroundColor: '#b52b2b',
                    color: '#ffffff',
                    border: '1px solid #bee5eb',
                    padding: '10px',
                    borderRadius: '5px',
                    marginBottom: '20px'
                }}>
                    {notification}
                </div>
            )}

            <section className="section">
                <h2>Manage Users</h2>
                <button onClick={() => setShowCreateModal(true)} className="create-button">
                    Create User
                </button>
                <table className="styled-table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {users.map((user) => (
                        <tr key={user.id}>
                            <td>{user.name}</td>
                            <td>{user.email}</td>
                            <td className="actions-column">
                                <button
                                    onClick={() => handleDeleteUser(user.id)}
                                    className="delete-button"
                                >
                                    Delete
                                </button>
                                <button
                                    onClick={() => handleEditUser(user)}
                                    className="create-button"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            </section>

            <section className="section">
                <h2>Manage Vacation Requests</h2>
                <table className="styled-table">
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {vacationRequests.map((request) => (
                        <tr key={request.id}>
                            <td>{request.user.name}</td>
                            <td>{new Date(request.startDate).toLocaleDateString()}</td>
                            <td>{new Date(request.endDate).toLocaleDateString()}</td>
                            <td>{request.status}</td>
                            <td className="actions-column">
                                {request.status === 'Pending' && (
                                    <>
                                        <button
                                            onClick={() => handleApprove(request.id)}
                                            className="approve-button"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            onClick={() => handleReject(request.id)}
                                            className="reject-button"
                                        >
                                            Reject
                                        </button>
                                    </>
                                )}
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            </section>

            <ConfirmModal
                show={showConfirmModal}
                onConfirm={confirmDelete}
                onCancel={cancelDelete}
                message="Are you sure you want to delete this user?"
            />

            <HandleUserModal
                show={showCreateModal}
                onClose={() => {
                    setShowCreateModal(false);
                    setSelectedUser(null);
                }}
                onCreate={handleCreateUser}
                onUpdate={handleUpdateUser}
                userToEdit={selectedUser}
            />
        </div>
    );
};

export default ManagerDashboard;
