using Microsoft.EntityFrameworkCore;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;
using OdontoCare.Domain.Entities;
using OdontoCare.Infrastructure.Data;

namespace OdontoCare.Infrastructure.Security;

public sealed class AuthService(OdontoCareDbContext db, IPasswordService passwords, ITokenService tokens) : IAuthService
{
    private static UsuarioDto Map(Usuario u) => new(u.IdUsuario, u.Nome, u.Email, u.Login, Roles.Normalizar(u.TipoUsuario));

    public async Task<LoginResponseDto?> LoginAsync(LoginDto dto)
    {
        var id = dto.Identificador.Trim();
        var usuario = await db.Usuarios.AsNoTracking()
            .FirstOrDefaultAsync(u => u.Login == id || u.Email == id);
        if (usuario is null || !passwords.Verify(dto.Senha, usuario.Senha)) return null;
        var role = Roles.Normalizar(usuario.TipoUsuario);
        if (!Roles.Valido(role)) return null;
        var (jwt, expiracao) = tokens.Gerar(usuario.IdUsuario, usuario.Nome, usuario.Email, role);
        return new LoginResponseDto(jwt, expiracao, Map(usuario));
    }

    public async Task<UsuarioDto> RegistrarAsync(RegistroUsuarioDto dto)
    {
        var role = Roles.Normalizar(dto.TipoUsuario);
        if (!Roles.Valido(role)) throw new BusinessException("Perfil de usuário inválido.", 400);
        var login = dto.Login.Trim();
        var email = string.IsNullOrWhiteSpace(dto.Email) ? null : dto.Email.Trim();
        if (await db.Usuarios.AnyAsync(u => u.Login == login || (email != null && u.Email == email)))
            throw new BusinessException("Login ou e-mail já está em uso.", 409);
        var usuario = new Usuario
        {
            Nome = dto.Nome.Trim(), Login = login, Email = email,
            Senha = passwords.Hash(dto.Senha), TipoUsuario = role
        };
        db.Usuarios.Add(usuario);
        await db.SaveChangesAsync();
        return Map(usuario);
    }
}
