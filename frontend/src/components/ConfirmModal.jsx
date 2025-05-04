import React from "react";

const ConfirmModal = ({ show, onConfirm, onCancel, message }) => {
    if (!show) return null;

    return (
        <div style={styles.overlay}>
            <div style={styles.modal}>
                <p>{message}</p>
                <div style={styles.buttons}>
                    <button onClick={onConfirm} style={{ ...styles.button, backgroundColor: 'red' }}>Yes</button>
                    <button onClick={onCancel} style={styles.button}>Cancel</button>
                </div>
            </div>
        </div>
    );
};

const styles = {
    overlay: {
        position: "fixed",
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        backgroundColor: "rgba(0,0,0,0.5)",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        zIndex: 9999,
    },
    modal: {
        backgroundColor: "#fff",
        padding: "20px",
        borderRadius: "8px",
        width: "300px",
        textAlign: "center",
    },
    buttons: {
        marginTop: "20px",
        display: "flex",
        justifyContent: "space-around",
    },
    button: {
        padding: "10px 20px",
        border: "none",
        borderRadius: "4px",
        cursor: "pointer",
    },
};

export default ConfirmModal;
