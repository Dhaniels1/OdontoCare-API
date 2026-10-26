using System.ComponentModel.DataAnnotations;
namespace OdontoCare.Application.DTOs;
public sealed class UsuarioUpdateDto
{
    [Required, MaxLength(200)] public string Nome { get; set; } = "";
    [EmailAddress, MaxLength(200)] public string? Email { get; set; }
    [Required, MinLength(3), MaxLength(50)] public string Login { get; set; } = "";
    public string? Senha { get; set; } // Vazio mantém a senha atual.
    [Required] public string TipoUsuario { get; set; } = "";
}
