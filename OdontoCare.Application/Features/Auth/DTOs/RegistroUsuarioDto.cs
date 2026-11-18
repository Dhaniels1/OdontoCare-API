using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Auth.DTOs;

public sealed class RegistroUsuarioDto
{
    [Required(ErrorMessage = "Nome é obrigatório."), MaxLength(200)] public string Nome { get; set; } = "";
    [EmailAddress(ErrorMessage = "E-mail inválido."), MaxLength(200)] public string? Email { get; set; }
    [Required, MaxLength(50), MinLength(3)] public string Login { get; set; } = "";
    [Required, MinLength(12, ErrorMessage = "A senha deve possuir no mínimo 12 caracteres."), MaxLength(128)] public string Senha { get; set; } = "";
    [Required] public string TipoUsuario { get; set; } = "";
}
