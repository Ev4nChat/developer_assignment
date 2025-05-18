import React, { useState, useContext, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import AuthContext from "../context/AuthContext";
import api from "../api/axios";
import { jwtDecode } from "jwt-decode";

const Login = () => {
    const navigate = useNavigate();
    const { login } = useContext(AuthContext);

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");

    useEffect(() => {
        document.title = "Login | Vacation Management Portal";
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");

        try {
            const response = await api.post(
                "/login",
                { email, password },
                { withCredentials: true }
            );

            const { token } = response.data;
            const decodedToken = jwtDecode(token);
            const userRoles = decodedToken.roles;

            login(token, userRoles[0]);

            if (userRoles.includes("ROLE_MANAGER")) {
                navigate("/manager/dashboard");
            } else if (userRoles.includes("ROLE_EMPLOYEE")) {
                navigate("/employee/dashboard");
            } else {
                setError("Unknown user role.");
            }
        } catch (error) {
            console.error(error);
            setError("Invalid email or password.");
        }
    };

    return (
        <div style={styles.wrapper}>
            <h1 style={styles.appTitle}>Vacation Management Portal</h1>
            <div style={styles.card}>
                <h2 style={styles.title}>Login</h2>
                {error && <p style={styles.error}>{error}</p>}
                <form onSubmit={handleSubmit} style={styles.form}>
                    <input
                        type="email"
                        placeholder="Email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        style={styles.input}
                    />
                    <input
                        type="password"
                        placeholder="Password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                        style={styles.input}
                    />
                    <button type="submit" style={styles.button}>Login</button>
                </form>
            </div>
        </div>
    );
};

const styles = {
    wrapper: {
        height: "100vh",
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        background: "#eef3f7", // Light background
        fontFamily: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
    },
    appTitle: {
        fontSize: "36px",
        marginBottom: "20px",
        color: "#2c3e50",
    },
    card: {
        background: "#fff",
        padding: "40px",
        borderRadius: "8px",
        boxShadow: "0 4px 12px rgba(0, 0, 0, 0.1)",
        width: "100%",
        maxWidth: "400px",
    },
    title: {
        textAlign: "center",
        marginBottom: "20px",
        color: "#2c3e50",
    },
    form: {
        display: "flex",
        flexDirection: "column",
        gap: "16px",
    },
    input: {
        padding: "12px",
        borderRadius: "4px",
        border: "1px solid #ccc",
        fontSize: "16px",
    },
    button: {
        backgroundColor: "#3498db",
        color: "#fff",
        padding: "12px",
        border: "none",
        borderRadius: "4px",
        fontSize: "16px",
        cursor: "pointer",
        transition: "background-color 0.3s ease",
    },
    error: {
        color: "#e74c3c",
        marginBottom: "10px",
        textAlign: "center",
    },
};

export default Login;
