using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;
namespace OdontoCare.Application.Services;
public sealed class FinanceiroService(IEntityRepository<Procedimento> procedimentos,
    IEntityRepository<Orcamento> orcamentos, IEntityRepository<Pagamento> pagamentos,
    IEntityRepository<Paciente> pacientes, IEntityRepository<Consulta> consultas,
    IEntityRepository<Material> materiais, IConsultaLookupService lookups)
{
    public async Task<List<Procedimento>> ProcedimentosAsync() => (await procedimentos.ListarAsync()).OrderBy(x => x.NomeProcedimento).ToList();
    public Task<Procedimento?> ProcedimentoAsync(int id) => procedimentos.BuscarAsync(x => x.IdProcedimento == id);
    public async Task<Procedimento?> GravarProcedimentoAsync(int? id, ProcedimentoGravarDto dto)
    {
        var p = id.HasValue ? await ProcedimentoAsync(id.Value) : new Procedimento();
        if(p is null) return null;
        p.NomeProcedimento=dto.NomeProcedimento.Trim(); p.Descricao=dto.Descricao?.Trim(); p.ValorProcedimento=dto.ValorProcedimento;
        if(!id.HasValue) procedimentos.Adicionar(p);
        await procedimentos.SalvarAsync(); return p;
    }
    public async Task<bool> ExcluirProcedimentoAsync(int id)
    {
        var p=await ProcedimentoAsync(id); if(p is null) return false;
        if(await consultas.ExisteAsync(c => c.IdProcedimento == id)) throw new BusinessException("Procedimento possui consultas vinculadas.");
        procedimentos.Remover(p); await procedimentos.SalvarAsync(); return true;
    }
    public async Task<List<OrcamentoResponseDto>> OrcamentosAsync()
    {
        var nomes=(await pacientes.ListarAsync()).ToDictionary(p => p.IdPaciente, p => p.Nome);
        return (await orcamentos.ListarAsync()).OrderByDescending(o => o.DataOrcamento)
            .Select(o => Map(o, nomes.GetValueOrDefault(o.IdPaciente, ""))).ToList();
    }
    private static OrcamentoResponseDto Map(Orcamento o, string nome) => new(o.IdOrcamento, o.ValorTotal, o.DataOrcamento, o.StatusOrcamento, o.Observacoes, o.IdPaciente, nome);
    public async Task<OrcamentoResponseDto?> OrcamentoAsync(int id)
    {
        var o=await orcamentos.BuscarAsync(x => x.IdOrcamento == id);
        return o is null ? null : Map(o, (await pacientes.BuscarAsync(p => p.IdPaciente == o.IdPaciente))?.Nome ?? "");
    }
    public async Task<OrcamentoResponseDto?> GravarOrcamentoAsync(int? id, OrcamentoGravarDto dto)
    {
        var o=id.HasValue ? await orcamentos.BuscarAsync(x => x.IdOrcamento == id) : new Orcamento();
        if(o is null) return null;
        var p=await pacientes.BuscarAsync(x => x.IdPaciente == dto.IdPaciente);
        if(p is null) throw new BusinessException("Paciente informado não existe.", 400);
        o.ValorTotal=dto.ValorTotal!.Value; o.DataOrcamento=dto.DataOrcamento!.Value;
        o.StatusOrcamento=dto.StatusOrcamento; o.Observacoes=dto.Observacoes?.Trim(); o.IdPaciente=dto.IdPaciente;
        if(!id.HasValue) orcamentos.Adicionar(o);
        await orcamentos.SalvarAsync(); return Map(o,p.Nome);
    }
    public async Task<bool> ExcluirOrcamentoAsync(int id)
    {
        var o=await orcamentos.BuscarAsync(x => x.IdOrcamento == id); if(o is null) return false;
        orcamentos.Remover(o); await orcamentos.SalvarAsync(); return true;
    }
    public async Task<List<PagamentoResponseDto>> PagamentosAsync(int? consultaId = null)
    {
        var nomes=(await lookups.ListarAsync(null)).ToDictionary(c => c.IdConsulta);
        return (await pagamentos.ListarAsync(p => consultaId == null || p.IdConsulta == consultaId)).OrderByDescending(p => p.DataPagamento)
            .ThenBy(p => p.IdPagamento).Select(p => Map(p, nomes.GetValueOrDefault(p.IdConsulta))).ToList();
    }
    private static PagamentoResponseDto Map(Pagamento p, ConsultaListaDto? c) => new(p.IdPagamento,p.ValorTotal,p.FormaPagamento,p.StatusPagamento,p.DataPagamento,p.IdConsulta,c?.NomePaciente ?? "",c?.NomeProcedimento ?? "");
    public async Task<PagamentoResponseDto?> PagamentoAsync(int id)
    {
        var p=await pagamentos.BuscarAsync(x => x.IdPagamento == id); if(p is null) return null;
        var c=(await lookups.ListarAsync(null)).FirstOrDefault(c => c.IdConsulta == p.IdConsulta);
        return Map(p,c);
    }
    public async Task<PagamentoResponseDto?> GravarPagamentoAsync(int? id, PagamentoGravarDto dto)
    {
        var p=id.HasValue ? await pagamentos.BuscarAsync(x => x.IdPagamento == id) : new Pagamento();
        if(p is null) return null;
        if(!await consultas.ExisteAsync(c => c.IdConsulta == dto.IdConsulta)) throw new BusinessException("Consulta informada não existe.", 400);
        p.ValorTotal=dto.ValorTotal!.Value; p.FormaPagamento=dto.FormaPagamento; p.StatusPagamento=dto.StatusPagamento;
        p.DataPagamento=dto.DataPagamento; p.IdConsulta=dto.IdConsulta;
        if(!id.HasValue) pagamentos.Adicionar(p);
        await pagamentos.SalvarAsync(); return await PagamentoAsync(p.IdPagamento);
    }
    public async Task<bool> ExcluirPagamentoAsync(int id)
    {
        var p=await pagamentos.BuscarAsync(x => x.IdPagamento == id); if(p is null) return false;
        pagamentos.Remover(p); await pagamentos.SalvarAsync(); return true;
    }
    public async Task<ReciboDto?> ReciboAsync(int id)
    {
        var p=await PagamentoAsync(id); if(p is null) return null;
        var c=await consultas.BuscarAsync(x => x.IdConsulta == p.IdConsulta);
        var paciente=c is null ? null : await pacientes.BuscarAsync(x => x.IdPaciente == c.IdPaciente);
        return new ReciboDto(p, paciente?.Cpf ?? "");
    }
    public async Task<PainelDto> PainelAsync(int? usuarioId, DateTime hoje)
    {
        var agenda=await lookups.ListarAsync(usuarioId);
        var valores=usuarioId.HasValue ? new List<Pagamento>() : await pagamentos.ListarAsync();
        var estoque=await materiais.ListarAsync();
        return new PainelDto(usuarioId.HasValue ? agenda.Select(c=>c.IdPaciente).Distinct().Count() : (await pacientes.ListarAsync()).Count,
            agenda.Count,(await procedimentos.ListarAsync()).Count,valores.Count,estoque.Count,
            agenda.Count(c=>c.DataConsulta.Date==hoje.Date && c.StatusConsulta!="Cancelada"),
            valores.Where(p=>p.StatusPagamento=="Pago").Sum(p=>p.ValorTotal),
            valores.Where(p=>p.StatusPagamento=="Pendente").Sum(p=>p.ValorTotal),
            valores.Where(p=>p.StatusPagamento=="Cancelado").Sum(p=>p.ValorTotal),
            estoque.Where(m=>m.Quantidade<=m.EstoqueMinimo).ToList(),
            agenda.GroupBy(c=>c.NomeProcedimento).Select(g=>new ProcedimentoResumoDto(g.Key,g.Count())).OrderByDescending(g=>g.Total).ToList(),
            agenda.Where(c=>c.DataConsulta.Date>=hoje.Date && c.StatusConsulta!="Cancelada").Take(5).ToList());
    }
}
