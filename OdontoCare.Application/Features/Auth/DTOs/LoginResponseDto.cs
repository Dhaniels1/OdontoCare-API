using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Auth.DTOs;

public sealed record LoginResponseDto(string Token, DateTime ExpiraEmUtc, UsuarioDto Usuario);
