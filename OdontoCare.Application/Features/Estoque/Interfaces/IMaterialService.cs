using OdontoCare.Application.Common.Models;
using OdontoCare.Application.Features.Estoque.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Estoque.Interfaces;

public interface IMaterialService
{
    Task<PaginaDto<MaterialDto>> ListarAsync(int pagina, int tamanho, string? busca, bool? estoqueBaixo);
    Task<MaterialDto?> BuscarAsync(int id);
    Task<MaterialDto> CriarAsync(MaterialGravarDto dto);
    Task<MaterialDto?> AtualizarAsync(int id, MaterialGravarDto dto);
    Task<bool> ExcluirAsync(int id);
    Task<MaterialDto?> MovimentarAsync(int id, string tipo, int quantidade, int usuario);
    Task<IReadOnlyList<MovimentacaoDto>> HistoricoAsync(int materialId);
}
