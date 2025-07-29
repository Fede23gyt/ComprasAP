import Swal from 'sweetalert2';

export function useConfirm() {
    return (title, text, confirmText = 'Confirmar') =>
        Swal.fire({
            title,
            text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0B4F6C',
            cancelButtonColor: '#6B7280',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancelar',
        });
}
