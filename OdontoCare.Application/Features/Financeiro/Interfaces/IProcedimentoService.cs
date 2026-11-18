using OdontoCare.Application.Features.Financeiro.DTOs;

namespace OdontoCare.Application.Features.Financeiro.Interfaces;

public interface IProcedimentoService
{
    Task<List<ProcedimentoDetalheDto>> ListarAsync();
    Task<ProcedimentoDetalheDto?> BuscarAsync(int id);
    Task<ProcedimentoDetalheDto?> GravarAsync(int? id, ProcedimentoGravarDto dto);
    Task<bool> ExcluirAsync(int id);
}
