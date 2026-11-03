using OdontoCare.Application.Common;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;
namespace OdontoCare.Application.Services;
public sealed class ConsultaService(IEntityRepository<Consulta> consultas, IEntityRepository<Paciente> pacientes,
    IEntityRepository<Usuario> usuarios, IEntityRepository<Procedimento> procedimentos,
    IAgendaLock agenda, HorarioClinica horario) : IConsultaService
{
    public Task<List<Consulta>> GetTodasAsync() => consultas.ListarAsync();
    public Task<Consulta?> GetPorIdAsync(int id) => consultas.BuscarAsync(c => c.IdConsulta == id);
    public async Task<Consulta> CriarAsync(Consulta consulta)
    {
        await using var tx = await agenda.IniciarAsync();
        await ValidarAsync(consulta, null);
        consultas.Adicionar(consulta); await consultas.SalvarAsync(); await tx.CommitAsync();
        return consulta;
    }
    public async Task<bool> AtualizarAsync(int id, Consulta dados)
    {
        await using var tx = await agenda.IniciarAsync();
        var c = await GetPorIdAsync(id); if (c is null) return false;
        await ValidarAsync(dados, id);
        c.DataConsulta=dados.DataConsulta; c.HorarioConsulta=dados.HorarioConsulta;
        c.StatusConsulta=dados.StatusConsulta; c.Observacoes=dados.Observacoes;
        c.IdUsuario=dados.IdUsuario; c.IdPaciente=dados.IdPaciente; c.IdProcedimento=dados.IdProcedimento;
        await consultas.SalvarAsync(); await tx.CommitAsync(); return true;
    }
    public async Task<bool> ExcluirAsync(int id)
    {
        var c=await GetPorIdAsync(id); if(c is null) return false;
        c.StatusConsulta="Cancelada"; await consultas.SalvarAsync(); return true;
    }
    public Task<bool> CancelarAsync(int id) => ExcluirAsync(id);
    private async Task ValidarAsync(Consulta c, int? ignorarId)
    {
        if (!await pacientes.ExisteAsync(p => p.IdPaciente == c.IdPaciente)) throw new BusinessException("O paciente informado não existe.");
        if (!await usuarios.ExisteAsync(u => u.IdUsuario == c.IdUsuario)) throw new BusinessException("O usuário informado não existe.");
        if (!await procedimentos.ExisteAsync(p => p.IdProcedimento == c.IdProcedimento)) throw new BusinessException("O procedimento informado não existe.");
        await agenda.BloquearAsync(c.IdPaciente, c.IdUsuario);
        if(c.DataConsulta == default || c.HorarioConsulta < TimeSpan.Zero || c.HorarioConsulta >= TimeSpan.FromDays(1))
            throw new BusinessException("Data ou horário inválido.");
        if(horario.Inicio >= horario.Fim) throw new BusinessException("Horário da clínica não configurado corretamente.");
        if(c.HorarioConsulta < horario.Inicio || c.HorarioConsulta >= horario.Fim)
            throw new BusinessException("Agendamento fora do horário de funcionamento configurado.");
        if(await consultas.ExisteAsync(x => (!ignorarId.HasValue || x.IdConsulta != ignorarId.Value) &&
            x.DataConsulta.Date == c.DataConsulta.Date && x.HorarioConsulta == c.HorarioConsulta &&
            x.StatusConsulta != "Cancelada" && (x.IdUsuario == c.IdUsuario || x.IdPaciente == c.IdPaciente)))
            throw new BusinessException("Paciente ou profissional já possui consulta neste horário.");
    }
}
