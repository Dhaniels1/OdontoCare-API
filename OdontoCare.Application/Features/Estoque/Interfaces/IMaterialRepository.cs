using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Estoque.Interfaces;

public interface IMaterialRepository
{
    Task<List<Material>> ListarAsync();
    Task<Material?> BuscarAsync(int id);
    Task<Material> CriarAsync(Material material);
    Task<bool> AtualizarAsync(Material material);
    Task<bool> ExcluirAsync(int id);
    Task<Material?> MovimentarAsync(int id, string tipo, int quantidade, int usuario);
    Task<List<MovimentacaoEstoque>> HistoricoAsync(int materialId);
}
