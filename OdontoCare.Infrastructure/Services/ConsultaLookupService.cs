using Microsoft.EntityFrameworkCore;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Infrastructure.Data;

namespace OdontoCare.Infrastructure.Services;
public sealed class ConsultaLookupService(OdontoCareDbContext db) : IConsultaLookupService
{
    public async Task<List<ConsultaListaDto>> ListarAsync(int? usuarioId)
    {
        var consulta = from c in db.Consultas.AsNoTracking()
            join p in db.Pacientes.AsNoTracking() on c.IdPaciente equals p.IdPaciente
            join u in db.Usuarios.AsNoTracking() on c.IdUsuario equals u.IdUsuario
            join pr in db.Procedimentos.AsNoTracking() on c.IdProcedimento equals pr.IdProcedimento
            where usuarioId == null || c.IdUsuario == usuarioId
            orderby c.DataConsulta, c.HorarioConsulta
            select new ConsultaListaDto(c.IdConsulta, c.DataConsulta, c.HorarioConsulta,
                c.StatusConsulta, c.Observacoes, c.IdUsuario, c.IdPaciente, c.IdProcedimento,
                u.Nome, p.Nome, pr.NomeProcedimento);
        return await consulta.ToListAsync();
    }
    public async Task<List<UsuarioDto>> ListarUsuariosAsync() => await db.Usuarios.AsNoTracking()
        .OrderBy(u => u.Nome).Select(u => new UsuarioDto(u.IdUsuario, u.Nome, u.Email, u.Login, u.TipoUsuario))
        .ToListAsync();
    public async Task<List<ProcedimentoDto>> ListarProcedimentosAsync() => await db.Procedimentos.AsNoTracking()
        .OrderBy(x => x.NomeProcedimento)
        .Select(x => new ProcedimentoDto(x.IdProcedimento, x.NomeProcedimento)).ToListAsync();
    public async Task<UsuarioDto?> BuscarUsuarioAsync(int id) => await db.Usuarios.AsNoTracking()
        .Where(x => x.IdUsuario == id).Select(u => new UsuarioDto(u.IdUsuario, u.Nome, u.Email, u.Login, u.TipoUsuario))
        .FirstOrDefaultAsync();
}
