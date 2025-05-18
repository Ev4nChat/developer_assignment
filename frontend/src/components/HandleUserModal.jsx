import React, { useState, useEffect } from 'react';

const HandleUserModal = ({ show, onClose, onCreate, onUpdate, userToEdit }) => {
    const [form, setForm] = useState({
        name: '',
        email: '',
        password: '',
        employeeCode: '',
        roles: ['ROLE_EMPLOYEE'],
    });

    const [error, setError] = useState(null);

    useEffect(() => {
        if (userToEdit) {
            setForm({
                name: userToEdit.name || '',
                email: userToEdit.email || '',
                password: '',
                employeeCode: userToEdit.employeeCode || '',
                roles: userToEdit.roles || ['ROLE_EMPLOYEE'],
            });
        } else {
            setForm({
                name: '',
                email: '',
                password: '',
                employeeCode: '',
                roles: ['ROLE_EMPLOYEE'],
            });
        }

        setError(null); // Clear error when modal is opened
    }, [userToEdit, show]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null); // Reset error

        try {
            if (userToEdit) {
                const updatedData = {
                    name: form.name,
                    email: form.email,
                    employeeCode: userToEdit.employeeCode,
                    roles: form.roles,
                };

                if (form.password) {
                    updatedData.password = form.password;
                }

                await onUpdate(updatedData);
            } else {
                await onCreate(form);
            }

            onClose(); // Close only on success
        } catch (err) {
            // Handle duplicate or validation error
            if (err.response?.status === 400 || err.response?.status === 409) {
                setError("This email is already in use. Please use a different one.");
            } else {
                setError("Something went wrong. Please try again.");
            }
        }
    };

    if (!show) return null;

    return (
        <div style={{
            position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
            backgroundColor: 'rgba(0,0,0,0.3)', display: 'flex', justifyContent: 'center', alignItems: 'center'
        }}>
            <div style={{ background: 'white', padding: 20, width: 400 }}>
                <h3>{userToEdit ? 'Edit User' : 'Create New User'}</h3>

                {/* Notification box */}
                {error && (
                    <div style={{
                        backgroundColor: '#ffe0e0',
                        padding: '10px',
                        marginBottom: '10px',
                        color: '#b00020',
                        borderRadius: '5px',
                        border: '1px solid #f5c2c7'
                    }}>
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit}>
                    <div>
                        <label>Name:</label><br />
                        <input type="text" name="name" value={form.name} onChange={handleChange} required />
                    </div>
                    <div>
                        <label>Email:</label><br />
                        <input type="email" name="email" value={form.email} onChange={handleChange} required />
                    </div>
                    <div>
                        <label>Password:</label><br />
                        <input type="password" name="password" value={form.password} onChange={handleChange} required={!userToEdit} />
                    </div>
                    <div>
                        <label>Employee Code:</label><br />
                        <input
                            type="text"
                            name="employeeCode"
                            value={form.employeeCode}
                            onChange={handleChange}
                            disabled={!!userToEdit}
                            required={!userToEdit}
                        />
                    </div>
                    <div style={{ marginTop: 15 }}>
                        <button type="submit">{userToEdit ? '✅ Update' : '✅ Create'}</button>
                        <button type="button" onClick={onClose} style={{ marginLeft: 10 }}>Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default HandleUserModal;
