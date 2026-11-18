using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Auth.DTOs;

public sealed class LoginDto
{
    [Required(ErrorMessage = "Informe o login ou e-mail.")]
    public string Identificador { get; set; } = "";
    [Required(ErrorMessage = "Informe a senha.")]
    public string Senha { get; set; } = "";
}
