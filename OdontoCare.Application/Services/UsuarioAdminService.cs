using OdontoCare.Domain.Entities;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;

namespace OdontoCare.Application.Services;
public sealed class UsuarioAdminService(IEntityRepository<Usuario> usuarios, IEntityRepository<Consulta> consultas, IPasswordService password) : IUsuarioAdminService
{
    public async Task<UsuarioDto?> AtualizarAsync(int id, UsuarioUpdateDto dto)
    {
        var u = await usuarios.BuscarAsync(x => x.IdUsuario == id);
        if (u is null) return null;
        var perfil = Roles.Normalizar(dto.TipoUsuario);
        if (!Roles.Valido(perfil)) throw new BusinessException("Perfil inválido.", 400);
        if (await usuarios.ExisteAsync(x => x.IdUsuario != id &&
            (x.Login == dto.Login.Trim() || (dto.Email != null && x.Email == dto.Email.Trim()))))
            throw new BusinessException("Login ou e-mail já está em uso.");
        u.Nome = dto.Nome.Trim(); u.Email = dto.Email?.Trim(); u.Login = dto.Login.Trim();
        u.TipoUsuario = perfil;
        if (!string.IsNullOrWhiteSpace(dto.Senha))
        {
            if (dto.Senha.Length < 12) throw new BusinessException("Nova senha deve possuir no mínimo 12 caracteres.", 400);
            u.Senha = password.Hash(dto.Senha);
        }
        await usuarios.SalvarAsync();
        return new UsuarioDto(u.IdUsuario, u.Nome, u.Email, u.Login, perfil);
    }
    public async Task<bool> ExcluirAsync(int id, int executorId)
    {
        if (id == executorId) throw new BusinessException("Não é permitido excluir o próprio usuário administrador.");
        if (await consultas.ExisteAsync(x => x.IdUsuario == id))
            throw new BusinessException("Usuário possui consultas vinculadas e não pode ser excluído.");
        var u = await usuarios.BuscarAsync(x => x.IdUsuario == id);
        if (u is null) return false;
        usuarios.Remover(u); await usuarios.SalvarAsync(); return true;
    }
}
