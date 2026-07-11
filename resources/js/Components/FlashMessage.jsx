function FlashMessage({ message, type = 'success', className = '', ...props }) {
    if (!message) {
        return null;
    }

    const style = {
        success: 'bg-green-300 text-green-700',
        error: 'bg-red-300 text-red-700',
        warning: 'bg-yellow-300 text-yellow-700',
        info: 'bg-blue-300 text-blue-700',
    };

    return message ? (
        <div
            {...props}
            className={`mb-4 mt-4 rounded p-4 ${style[type]} ${className}`}
        >
            {message}
        </div>
    ) : null;
}

export default FlashMessage;
