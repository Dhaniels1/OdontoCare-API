using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Auth.DTOs;

public sealed record UsuarioDto(int IdUsuario, string Nome, string? Email, string Login, string TipoUsuario);
