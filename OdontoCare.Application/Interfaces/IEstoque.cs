using OdontoCare.Application.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Interfaces;

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

public interface IMaterialCache
{
    Task<List<MaterialDto>> ObterOuCarregarAsync(Func<Task<List<MaterialDto>>> carregar);
    void Invalidar();
}

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

public interface IEquipamentoService
{
    Task<List<EquipamentoDto>> ListarAsync();
    Task<EquipamentoDto?> BuscarAsync(int id);
    Task<EquipamentoDto> CriarAsync(EquipamentoGravarDto dto);
    Task<EquipamentoDto?> AtualizarAsync(int id, EquipamentoGravarDto dto);
    Task<bool> ExcluirAsync(int id);
}
