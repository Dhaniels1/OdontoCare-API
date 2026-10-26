using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.DTOs;

public sealed class LoginDto
{
    [Required(ErrorMessage = "Informe o login ou e-mail.")]
    public string Identificador { get; set; } = "";
    [Required(ErrorMessage = "Informe a senha.")]
    public string Senha { get; set; } = "";
}

public sealed class RegistroUsuarioDto
{
    [Required(ErrorMessage = "Nome é obrigatório."), MaxLength(200)] public string Nome { get; set; } = "";
    [EmailAddress(ErrorMessage = "E-mail inválido."), MaxLength(200)] public string? Email { get; set; }
    [Required, MaxLength(50), MinLength(3)] public string Login { get; set; } = "";
    [Required, MinLength(12, ErrorMessage = "A senha deve possuir no mínimo 12 caracteres."), MaxLength(128)] public string Senha { get; set; } = "";
    [Required] public string TipoUsuario { get; set; } = "";
}

public sealed record UsuarioDto(int IdUsuario, string Nome, string? Email, string Login, string TipoUsuario);
public sealed record LoginResponseDto(string Token, DateTime ExpiraEmUtc, UsuarioDto Usuario);
