using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Services;

public sealed class PagamentoService(IEntityRepository<Pagamento> pagamentos, IEntityRepository<Consulta> consultas,
    IEntityRepository<Paciente> pacientes, IConsultaLookupService lookups) : IPagamentoService
{
    private static PagamentoResponseDto Map(Pagamento p, ConsultaListaDto? c) => new(p.IdPagamento, p.ValorTotal,
        p.FormaPagamento, p.StatusPagamento, p.DataPagamento, p.IdConsulta,
        c?.NomePaciente ?? "", c?.NomeProcedimento ?? "");

    public async Task<List<PagamentoResponseDto>> ListarAsync(int? consultaId = null)
    {
        var nomes = (await lookups.ListarAsync(null)).ToDictionary(c => c.IdConsulta);
        return (await pagamentos.ListarAsync(p => consultaId == null || p.IdConsulta == consultaId))
            .OrderByDescending(p => p.DataPagamento).ThenBy(p => p.IdPagamento)
            .Select(p => Map(p, nomes.GetValueOrDefault(p.IdConsulta))).ToList();
    }

    public async Task<PagamentoResponseDto?> BuscarAsync(int id)
    {
        var p = await pagamentos.BuscarAsync(x => x.IdPagamento == id);
        if (p is null) return null;
        var c = (await lookups.ListarAsync(null)).FirstOrDefault(c => c.IdConsulta == p.IdConsulta);
        return Map(p, c);
    }

    public async Task<PagamentoResponseDto?> GravarAsync(int? id, PagamentoGravarDto dto)
    {
        var p = id.HasValue ? await pagamentos.BuscarAsync(x => x.IdPagamento == id) : new Pagamento();
        if (p is null) return null;
        if (!await consultas.ExisteAsync(c => c.IdConsulta == dto.IdConsulta))
            throw new BusinessException("Consulta informada não existe.", 400);
        p.ValorTotal = dto.ValorTotal!.Value; p.FormaPagamento = dto.FormaPagamento; p.StatusPagamento = dto.StatusPagamento;
        p.DataPagamento = dto.DataPagamento; p.IdConsulta = dto.IdConsulta;
        if (!id.HasValue) pagamentos.Adicionar(p);
        await pagamentos.SalvarAsync();
        return await BuscarAsync(p.IdPagamento);
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var p = await pagamentos.BuscarAsync(x => x.IdPagamento == id);
        if (p is null) return false;
        pagamentos.Remover(p);
        await pagamentos.SalvarAsync();
        return true;
    }

    public async Task<ReciboDto?> ReciboAsync(int id)
    {
        var p = await BuscarAsync(id);
        if (p is null) return null;
        var c = await consultas.BuscarAsync(x => x.IdConsulta == p.IdConsulta);
        var paciente = c is null ? null : await pacientes.BuscarAsync(x => x.IdPaciente == c.IdPaciente);
        return new ReciboDto(p, paciente?.Cpf ?? "");
    }
}
