using OdontoCare.Application.Common.Interfaces;
using OdontoCare.Application.Features.Consultas.Interfaces;
using OdontoCare.Application.Features.Consultas.Mappings;
using OdontoCare.Application.Features.Estoque.Mappings;
using OdontoCare.Application.Features.Financeiro.DTOs;
using OdontoCare.Application.Features.Financeiro.Interfaces;
using OdontoCare.Application.Features.Financeiro.Mappings;
using OdontoCare.Application.Features.Pacientes.Mappings;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Financeiro.Services;

public sealed class PainelService(IEntityRepository<Procedimento> procedimentos, IEntityRepository<Pagamento> pagamentos,
    IEntityRepository<Paciente> pacientes, IEntityRepository<Material> materiais,
    IConsultaLookupService lookups) : IPainelService
{
    public async Task<PainelDto> ObterAsync(int? usuarioId, DateTime hoje)
    {
        var agenda = await lookups.ListarAsync(usuarioId);
        var valores = usuarioId.HasValue ? new List<Pagamento>() : await pagamentos.ListarAsync();
        var estoque = await materiais.ListarAsync();
        var totalPacientes = usuarioId.HasValue
            ? agenda.Select(c => c.IdPaciente).Distinct().Count()
            : (await pacientes.ListarAsync()).Count;
        return new PainelDto(totalPacientes, agenda.Count, (await procedimentos.ListarAsync()).Count,
            valores.Count, estoque.Count,
            agenda.Count(c => c.DataConsulta.Date == hoje.Date && c.StatusConsulta != "Cancelada"),
            valores.Where(p => p.StatusPagamento == "Pago").Sum(p => p.ValorTotal),
            valores.Where(p => p.StatusPagamento == "Pendente").Sum(p => p.ValorTotal),
            valores.Where(p => p.StatusPagamento == "Cancelado").Sum(p => p.ValorTotal),
            estoque.Where(m => m.Quantidade <= m.EstoqueMinimo).Select(m => m.ToDto()).ToList(),
            agenda.GroupBy(c => c.NomeProcedimento).Select(g => new ProcedimentoResumoDto(g.Key, g.Count()))
                .OrderByDescending(g => g.Total).ToList(),
            agenda.Where(c => c.DataConsulta.Date >= hoje.Date && c.StatusConsulta != "Cancelada").Take(5).ToList());
    }
}
